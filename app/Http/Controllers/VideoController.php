<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoSubtitle;
use App\Models\SystemSetting;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use FFMpeg\FFProbe;

class VideoController extends Controller
{
    private string $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = config('services.video_api.base_url');
        // Apenas a view precisa de autenticação web
        $this->middleware('auth')->only(['goToVideos']);

        // Todos os métodos API usam verificação interna
        $this->middleware('internal.api')->except(['goToVideos']);
    }

    public function goToVideos()
    {
        $videos = Video::all();
        $appRoute = url('/');
        return view('videos', compact('videos', 'appRoute'));
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $videos = Video::query()
            ->when(Schema::hasTable('video_subtitles'), function ($query) {
                $query->with('subtitles');
            })
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->get()
            ->map(function ($video) {
                $isLocalVideo = str_starts_with((string) $video->api_id, 'local_');
                $isEffectivelyCached = $video->cached || $isLocalVideo;

                return [
                    'id' => $video->id,
                    'api_id' => $video->api_id,
                    'title' => $video->title,
                    'name' => $video->name,
                    'description' => $video->description,
                    'duration' => $video->duration,
                    'size' => $this->formatSize($video->size),
                    'status' => $video->status,
                    'cached' => $isEffectivelyCached,
                    'lastSync' => $video->last_sync ? Carbon::parse($video->last_sync)->format('d/m/Y H:i') : null,
                    'url' => $video->url,
                    'thumbnail_url' => $video->thumbnail_url,
                    'is_active' => $video->is_active,
                    'subtitles' => $video->subtitles->map(function ($subtitle) {
                        return [
                            'id' => $subtitle->id,
                            'label' => $subtitle->label,
                            'language' => $subtitle->language,
                            'url' => $subtitle->url,
                        ];
                    })->values(),
                ];
            });

        return response()->json([
            'videos' => $videos,
            'stats' => $this->getVideoStats()
        ]);
    }

    /**
     * Sincroniza vídeos com a API externa
     */
    public function sync(Request $request)
    {
        try {
            $videosDirectory = storage_path('app/public/videos');

            if (!File::exists($videosDirectory)) {
                File::makeDirectory($videosDirectory, 0755, true);
            }

            $allowedExtensions = ['mp4', 'avi', 'mov', 'wmv', 'mkv', 'webm'];
            $localFiles = collect(File::files($videosDirectory))
                ->filter(function (\SplFileInfo $file) use ($allowedExtensions) {
                    return in_array(strtolower($file->getExtension()), $allowedExtensions, true);
                })
                ->values();

            $filesByName = [];
            foreach ($localFiles as $file) {
                $filename = $file->getFilename();
                $key = strtolower($filename);
                $filesByName[$key] = [
                    'filename' => $filename,
                    'absolute_path' => $file->getPathname(),
                    'relative_path' => 'videos/' . $filename,
                    'url' => $this->buildVideoPublicUrl($filename),
                    'size' => $file->getSize() ?: 0,
                    'title' => pathinfo($filename, PATHINFO_FILENAME),
                    'api_id' => 'local_' . substr(md5($key), 0, 16),
                ];
            }

            $syncedCount = 0;
            $createdCount = 0;
            $deletedCount = 0;

            $localDbVideos = Video::where(function ($query) {
                $query->where('api_id', 'like', 'local_%')
                    ->orWhere('file_path', 'like', 'videos/%')
                    ->orWhere('url', 'like', '%/videos/%');
            })->orderBy('id')->get();

            foreach ($localDbVideos as $video) {
                $existingName = strtolower((string) ($video->name ?: basename((string) $video->file_path ?: (string) $video->url)));

                if (!isset($filesByName[$existingName])) {
                    $this->deleteVideosSafely(collect([$video]));
                    $deletedCount++;
                    continue;
                }

                $fileData = $filesByName[$existingName];
                $detectedDuration = $this->detectVideoDuration($fileData['absolute_path']);
                $duration = $detectedDuration ?: (!empty($video->duration) ? $video->duration : '0:00');

                $video->update([
                    'api_id' => $fileData['api_id'],
                    'title' => $video->title ?: $fileData['title'],
                    'name' => $fileData['filename'],
                    'description' => null,
                    'duration' => $duration,
                    'size' => $fileData['size'],
                    'url' => $fileData['url'],
                    'thumbnail_url' => null,
                    'status' => 'cached',
                    'cached' => true,
                    'file_path' => $fileData['relative_path'],
                    'is_active' => true,
                    'last_sync' => now(),
                ]);

                $syncedCount++;
                unset($filesByName[$existingName]);
            }

            foreach ($filesByName as $fileData) {
                $detectedDuration = $this->detectVideoDuration($fileData['absolute_path']);

                Video::create([
                    'api_id' => $fileData['api_id'],
                    'title' => $fileData['title'],
                    'name' => $fileData['filename'],
                    'description' => null,
                    'duration' => $detectedDuration ?: '0:00',
                    'size' => $fileData['size'],
                    'url' => $fileData['url'],
                    'thumbnail_url' => null,
                    'status' => 'cached',
                    'cached' => true,
                    'file_path' => $fileData['relative_path'],
                    'is_active' => true,
                    'last_sync' => now(),
                ]);

                $syncedCount++;
                $createdCount++;
            }

            $this->cleanupDuplicateLocalVideos();

            app(AuditLogService::class)->log('video.sync', 'success', [
                'synced' => $syncedCount,
                'created' => $createdCount,
                'deleted' => $deletedCount,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Sincronização concluída. {$syncedCount} vídeos sincronizados ({$createdCount} novos, {$deletedCount} removidos).",
                'updated' => $syncedCount,
                'created' => $createdCount,
                'deleted' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            app(AuditLogService::class)->log('video.sync', 'failed', [
                'error' => $e->getMessage(),
            ], 'error');
            return response()->json([
                'success' => false,
                'message' => 'Erro ao sincronizar vídeos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Faz download do vídeo para cache local
     */
    public function download($id)
    {
        $video = Video::findOrFail($id);

        try {
            $video->update([
                'status' => 'downloading',
                'cached' => false
            ]);

            // Faz download do vídeo da API
            $response = Http::get($video->url);

            if (!$response->successful()) {
                throw new \Exception('Erro ao baixar vídeo: ' . $response->status());
            }

            $filename = "video_{$video->api_id}_" . uniqid() . '.mp4';
            $path = "videos/{$filename}";

            // Salva o arquivo localmente
            Storage::disk('public')->put($path, $response->body());
            $absolutePath = Storage::disk('public')->path($path);
            $duration = $this->detectVideoDuration($absolutePath);

            $video->update([
                'status' => 'cached',
                'cached' => true,
                'file_path' => $path,
                'duration' => $duration ?? $video->duration,
                'last_sync' => now()
            ]);

            app(AuditLogService::class)->log('video.download', 'success', [
                'video_id' => $video->id,
                'api_id' => $video->api_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vídeo baixado com sucesso',
                'video' => $video
            ]);
        } catch (\Exception $e) {
            $video->update(['status' => 'error']);

            app(AuditLogService::class)->log('video.download', 'failed', [
                'video_id' => $video->id,
                'error' => $e->getMessage(),
            ], 'error');

            return response()->json([
                'success' => false,
                'message' => 'Erro ao baixar vídeo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove vídeo do cache local
     */
    public function removeFromCache($id)
    {
        $video = Video::findOrFail($id);

        try {
            $this->deleteLocalVideoFile($video);

            $video->update([
                'cached' => false,
                'status' => 'available',
                'file_path' => null
            ]);

            app(AuditLogService::class)->log('video.cache_remove', 'success', [
                'video_id' => $video->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vídeo removido do cache',
                'video' => $video
            ]);
        } catch (\Exception $e) {
            app(AuditLogService::class)->log('video.cache_remove', 'failed', [
                'video_id' => $video->id,
                'error' => $e->getMessage(),
            ], 'error');
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover vídeo do cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload de vídeo para a API
     */
    public function upload(Request $request)
    {

        $request->validate([
            'video' => 'required|file|mimetypes:video/mp4,video/avi,video/quicktime,video/x-ms-wmv,video/x-matroska,video/webm',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_seconds' => 'nullable|integer|min:1',
            'subtitles' => 'nullable|array',
            'subtitles.*' => 'file|mimes:srt,txt|mimetypes:text/plain,application/x-subrip',
        ]);

        // return $request;

        try {
            $file = $request->file('video');
            $fileSize = $file->getSize();

            // 1️⃣ Garante que existe o diretório storage/app/public/videos
            $destinationPath = storage_path('app/public/videos');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // 2️⃣ Gera um nome único para o vídeo
            $filename = time() . '_' . $file->getClientOriginalName();

            // 3️⃣ Move o vídeo para public/videos
            $file->move($destinationPath, $filename);

            // 4️⃣ Caminho completo do ficheiro
            $localFilePath = $this->buildVideoPublicUrl($filename);
            $absoluteVideoPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;

            $duration = $this->detectVideoDuration($absoluteVideoPath);

            if (!$duration && $request->filled('duration_seconds')) {
                $duration = $this->formatDurationFromSeconds((float) $request->integer('duration_seconds'));
            }

            if (!$duration) {
                throw new \RuntimeException('Não foi possível identificar a duração do vídeo. Envie novamente após o carregamento completo do arquivo.');
            }

            // 6️⃣ Cria registro local
            $video = Video::create([
                'api_id' => 'local_' . uniqid(),
                'title' => $request->title,
                'name' => $filename,
                'description' => $request->description,
                'duration' => $duration,
                'size' => $fileSize,
                'url' => $localFilePath,
                'thumbnail_url' => null, // Poderia gerar thumbnail depois
                'status' => 'cached',
                'cached' => true,
                'file_path' => 'videos/' . $filename,
                'is_active' => true,
                'last_sync' => now()
            ]);

            if ($request->hasFile('subtitles')) {
                $this->storeVideoSubtitles($video, $request->file('subtitles'));
            }

            app(AuditLogService::class)->log('video.upload', 'success', [
                'video_id' => $video->id,
                'title' => $video->title,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vídeo enviado com sucesso',
                'video' => Schema::hasTable('video_subtitles') ? $video->load('subtitles') : $video
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar vídeo', [
                'message' => $e->getMessage(),
                'file' => $request->file('video') ? $request->file('video')->getClientOriginalName() : null,
                'size' => null,
            ]);
            app(AuditLogService::class)->log('video.upload', 'failed', [
                'error' => $e->getMessage(),
            ], 'error');
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar vídeo: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Atualiza metadados do vídeo
     */
    public function update(Request $request, $id)
    {
        $video = Video::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        try {
            $video->update($data);

            app(AuditLogService::class)->log('video.update', 'success', [
                'video_id' => $video->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vídeo atualizado com sucesso',
                'video' => $video,
            ]);
        } catch (\Exception $e) {
            app(AuditLogService::class)->log('video.update', 'failed', [
                'video_id' => $video->id,
                'error' => $e->getMessage(),
            ], 'error');
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar vídeo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove vídeo (local e na API)
     */
    public function destroy($id)
    {
        $video = Video::findOrFail($id);

        try {
            $isLocalVideo = str_starts_with((string) $video->api_id, 'local_');

            if (!$isLocalVideo) {
                try {
                    Http::timeout(10)->delete("{$this->apiBaseUrl}/videos/{$video->api_id}");
                } catch (\Throwable $apiError) {
                    Log::warning('Falha ao excluir vídeo na API externa', [
                        'video_id' => $video->id,
                        'api_id' => $video->api_id,
                        'error' => $apiError->getMessage(),
                    ]);
                }
            }

            // Remove localmente
            $this->deleteLocalVideoFile($video);
            $this->deleteVideoSubtitleFiles($video);

            $video->delete();

            app(AuditLogService::class)->log('video.delete', 'success', [
                'video_id' => $video->id,
                'api_id' => $video->api_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vídeo removido com sucesso'
            ]);
        } catch (\Exception $e) {
            app(AuditLogService::class)->log('video.delete', 'failed', [
                'video_id' => $video->id,
                'error' => $e->getMessage(),
            ], 'error');
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover vídeo: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getVideoStats()
    {
        $totalVideos = Video::count();
        $cacheQuery = Video::query()->where(function ($query) {
            $query->where('cached', true)
                ->orWhere('api_id', 'like', 'local_%');
        });
        $cachedVideos = (clone $cacheQuery)->count();
        $totalSize = (clone $cacheQuery)->sum('size');
        $activeVideos = Video::where('is_active', true)->count();

        return [
            'total_videos' => $totalVideos,
            'cached_videos' => $cachedVideos,
            'active_videos' => $activeVideos,
            'total_size' => $this->formatSize($totalSize),
            'api_status' => $this->checkApiStatus()
        ];
    }

    private function checkApiStatus()
    {
        $healthUrls = [];

        // 1) URL base configurada em services.php
        if (!empty($this->apiBaseUrl)) {
            $healthUrls[] = rtrim($this->apiBaseUrl, '/') . '/health';
        }

        // 2) Endpoint configurado em services.php (com tentativa de derivar /health)
        $configuredEndpoint = config('services.video_api.endpoint');
        if (!empty($configuredEndpoint)) {
            $healthUrls[] = $configuredEndpoint;
            $healthUrls[] = preg_replace('#/videos/?$#', '/health', rtrim($configuredEndpoint, '/')) ?: '';
        }

        // 3) Endpoint salvo nas configurações do sistema (banco)
        $savedEndpoint = optional(SystemSetting::first())->api_endpoint;
        if (!empty($savedEndpoint)) {
            $healthUrls[] = $savedEndpoint;
            $healthUrls[] = preg_replace('#/videos/?$#', '/health', rtrim($savedEndpoint, '/')) ?: '';
        }

        // 4) Fallback local da própria aplicação
        $healthUrls[] = url('/api/health');

        // Remove entradas vazias e duplicadas
        $healthUrls = array_values(array_unique(array_filter($healthUrls)));

        foreach ($healthUrls as $url) {
            try {
                $response = Http::timeout(5)->get($url);

                // 2xx => online, e 4xx também indica serviço acessível (apenas sem autorização/rota específica)
                if ($response->successful() || ($response->status() >= 400 && $response->status() < 500)) {
                    return 'online';
                }
            } catch (\Throwable $e) {
                // Tenta próxima URL
            }
        }

        return 'offline';
    }

    private function formatSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } else {
            return number_format($bytes / 1024, 2) . ' KB';
        }
    }

    private function detectVideoDuration(string $videoPath): ?string
    {
        $seconds = $this->getDurationFromFfprobeCli($videoPath);

        if ($seconds === null) {
            $seconds = $this->getDurationFromPhpFfmpeg($videoPath);
        }

        if ($seconds === null) {
            $seconds = $this->getDurationFromFfmpegCli($videoPath);
        }

        return $seconds !== null ? $this->formatDurationFromSeconds($seconds) : null;
    }

    private function getDurationFromFfprobeCli(string $videoPath): ?float
    {
        $escapedPath = escapeshellarg($videoPath);
        $output = @shell_exec("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 {$escapedPath} 2>/dev/null");

        if (!is_string($output)) {
            return null;
        }

        $seconds = (float) trim($output);
        return $seconds > 0 ? $seconds : null;
    }

    private function getDurationFromPhpFfmpeg(string $videoPath): ?float
    {
        try {
            $ffprobe = FFProbe::create();
            $duration = $ffprobe->format($videoPath)->get('duration');

            if ($duration === null) {
                return null;
            }

            $seconds = (float) $duration;
            return $seconds > 0 ? $seconds : null;
        } catch (\Throwable $e) {
            Log::warning('Falha ao detectar duração com php-ffmpeg', [
                'path' => $videoPath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function getDurationFromFfmpegCli(string $videoPath): ?float
    {
        $escapedPath = escapeshellarg($videoPath);
        $output = @shell_exec("ffmpeg -i {$escapedPath} 2>&1");

        if (!is_string($output) || !preg_match('/Duration:\\s*(\\d{2}):(\\d{2}):(\\d{2}(?:\\.\\d+)?)/', $output, $matches)) {
            return null;
        }

        $hours = (int) $matches[1];
        $minutes = (int) $matches[2];
        $seconds = (float) $matches[3];

        $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
        return $totalSeconds > 0 ? $totalSeconds : null;
    }

    private function formatDurationFromSeconds(float $seconds): string
    {
        $totalSeconds = (int) round($seconds);
        $hours = intdiv($totalSeconds, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);
        $remainingSeconds = $totalSeconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $remainingSeconds);
        }

        return sprintf('%d:%02d', $minutes, $remainingSeconds);
    }

    private function cleanupDuplicateLocalVideos(): void
    {
        $localVideos = Video::where(function ($query) {
            $query->where('api_id', 'like', 'local_%')
                ->orWhere('file_path', 'like', 'videos/%')
                ->orWhere('url', 'like', '%/videos/%');
        })->orderBy('id')->get();

        $groups = $localVideos->groupBy(function ($video) {
            $key = $video->file_path ?: ('videos/' . $video->name);
            return strtolower((string) $key);
        });

        foreach ($groups as $group) {
            if ($group->count() <= 1) {
                continue;
            }

            $primary = $group->first();
            $duplicateIds = $group->slice(1)->pluck('id')->values();

            if ($duplicateIds->isEmpty()) {
                continue;
            }

            if (Schema::hasColumn('schedules', 'video_id')) {
                DB::table('schedules')
                    ->whereIn('video_id', $duplicateIds->all())
                    ->update(['video_id' => $primary->id]);
            }

            Video::whereIn('id', $duplicateIds->all())->delete();
        }
    }

    private function deleteVideosSafely($videos): void
    {
        $ids = $videos->pluck('id')->values();

        if ($ids->isEmpty()) {
            return;
        }

        if (Schema::hasColumn('schedules', 'video_id')) {
            DB::table('schedules')
                ->whereIn('video_id', $ids->all())
                ->update(['video_id' => null]);
        }

        Video::whereIn('id', $ids->all())->delete();
    }

    private function deleteLocalVideoFile(Video $video): void
    {
        $candidates = [];

        if (!empty($video->file_path)) {
            $candidates[] = public_path($video->file_path);
            $candidates[] = Storage::disk('public')->path($video->file_path);
        }

        if (!empty($video->name)) {
            // Try both old public path (for backwards compatibility) and new storage path
            $candidates[] = public_path('videos/' . $video->name);
            $candidates[] = storage_path('app/public/videos/' . $video->name);
            $candidates[] = Storage::disk('public')->path('videos/' . $video->name);
        }

        foreach (array_unique($candidates) as $path) {
            if (!empty($path) && file_exists($path)) {
                @unlink($path);
            }
        }
    }

    private function buildVideoPublicUrl(string $filename): string
    {
        $safeFilename = rawurlencode(ltrim($filename, '/'));
        // Using storage path for consistency: asset('storage/videos/filename.mp4')
        return asset('storage/videos/' . $safeFilename);
    }

    private function buildSubtitlePublicUrl(string $path): string
    {
        $safePath = implode('/', array_map('rawurlencode', array_map('rawurldecode', explode('/', ltrim($path, '/')))));
        return asset('storage/' . $safePath);
    }

    private function storeVideoSubtitles(Video $video, array $files): void
    {
        if (!Schema::hasTable('video_subtitles')) {
            return;
        }

        $subtitleDir = 'subtitles/video_' . $video->id;

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType();
            $fileSize = $file->getSize();
            $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $storedPath = $file->storeAs($subtitleDir, $safeName, 'public');

            if (!$storedPath) {
                continue;
            }

            if (!$fileSize) {
                $fileSize = Storage::disk('public')->exists($storedPath)
                    ? Storage::disk('public')->size($storedPath)
                    : null;
            }

            VideoSubtitle::create([
                'video_id' => $video->id,
                'label' => pathinfo($originalName, PATHINFO_FILENAME),
                'language' => null,
                'path' => $storedPath,
                'url' => $this->buildSubtitlePublicUrl($storedPath),
                'mime' => $mimeType,
                'size' => $fileSize,
            ]);
        }
    }

    private function deleteVideoSubtitleFiles(Video $video): void
    {
        $directory = 'subtitles/video_' . $video->id;
        Storage::disk('public')->deleteDirectory($directory);
    }

    private function resolvePublicBaseUrl(): string
    {
        // 1) Host real da requisição atual (quando disponível)
        if (app()->bound('request')) {
            $request = request();
            $host = trim((string) $request->getSchemeAndHttpHost());

            if (!empty($host) && !str_contains($host, 'localhost')) {
                return rtrim($host, '/');
            }
        }

        // 2) Configuração explícita da API (geralmente aponta para host correto)
        $apiBaseUrl = trim((string) config('services.video_api.base_url', ''));
        if (!empty($apiBaseUrl)) {
            $normalized = rtrim($apiBaseUrl, '/');
            return preg_replace('#/api/?$#', '', $normalized) ?: $normalized;
        }

        $apiEndpoint = trim((string) config('services.video_api.endpoint', ''));
        if (!empty($apiEndpoint)) {
            $normalized = rtrim($apiEndpoint, '/');
            $withoutVideos = preg_replace('#/videos/?$#', '', $normalized) ?: $normalized;
            return preg_replace('#/api/?$#', '', $withoutVideos) ?: $withoutVideos;
        }

        // 3) Último fallback: APP_URL
        return rtrim((string) config('app.url'), '/');
    }
}
