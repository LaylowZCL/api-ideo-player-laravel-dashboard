<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class VideoController extends Controller
{
    private $apiBaseUrl = 'https://dev.fernandozucula.com/api';

    public function __construct()
    {
        $this->middleware('auth');
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
            ->when($search, function($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                             ->orWhere('name', 'like', "%{$search}%");
            })
            ->get()
            ->map(function($video) {
                return [
                    'id' => $video->id,
                    'api_id' => $video->api_id,
                    'title' => $video->title,
                    'name' => $video->name,
                    'description' => $video->description,
                    'duration' => $video->duration,
                    'size' => $this->formatSize($video->size),
                    'status' => $video->status,
                    'cached' => $video->cached,
                    'lastSync' => $video->last_sync ? Carbon::parse($video->last_sync)->format('d/m/Y H:i') : null,
                    'url' => $video->url,
                    'thumbnail_url' => $video->thumbnail_url,
                    'is_active' => $video->is_active,
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
            /*
            $response = Http::get("{$this->apiBaseUrl}/videos");
            
            if (!$response->successful()) {
                throw new \Exception('Erro ao conectar com a API: ' . $response->status());
            }
*/
            $apiVideos = Video::all();
            $syncedCount = 0;

            foreach ($apiVideos as $apiVideo) {
                Video::updateOrCreate(
                    ['api_id' => $apiVideo['id']],
                    [
                        'title' => $apiVideo['title'],
                        'name' => $apiVideo['filename'],
                        'description' => $apiVideo['description'] ?? null,
                        'duration' => $apiVideo['duration'] ?? '0:00',
                        'size' => $apiVideo['size'] ?? 0,
                        'url' => $apiVideo['url'],
                        'thumbnail_url' => $apiVideo['thumbnail_url'] ?? null,
                        'status' => 'available',
                        'is_active' => $apiVideo['is_active'] ?? true,
                        'last_sync' => now()
                    ]
                );
                $syncedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Sincronização concluída. {$syncedCount} vídeos atualizados.",
                'updated' => $syncedCount
            ]);

        } catch (\Exception $e) {
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

            $video->update([
                'status' => 'cached',
                'cached' => true,
                'file_path' => $path,
                'last_sync' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vídeo baixado com sucesso',
                'video' => $video
            ]);

        } catch (\Exception $e) {
            $video->update(['status' => 'error']);

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
            if ($video->cached && $video->file_path && Storage::disk('public')->exists($video->file_path)) {
                Storage::disk('public')->delete($video->file_path);
            }

            $video->update([
                'cached' => false,
                'status' => 'available',
                'file_path' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vídeo removido do cache',
                'video' => $video
            ]);

        } catch (\Exception $e) {
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
            'video' => 'required|file|mimetypes:video/mp4,video/avi,video/mov,video/wmv',
            // 'video' => 'required|file',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        try {
            $file = $request->file('video');

            // 1️⃣ Garante que existe o diretório public/videos
            $destinationPath = public_path('videos');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // 2️⃣ Gera um nome único para o vídeo
            $filename = time() . '_' . $file->getClientOriginalName();

            // 3️⃣ Move o vídeo para public/videos
            $file->move($destinationPath, $filename);

            // 4️⃣ Caminho completo do ficheiro
            $localFilePath = url('/videos') . '/' . $filename;

            // 6️⃣ Cria registro local
            $video = Video::create([
                'api_id' => 'local_' . uniqid(),
                'title' => $request->title,
                'name' => $filename,
                'description' => $request->description,
                'duration' => '0:00',
                'size' => /*filesize($localFilePath) ??*/ '3000',
                'url' => $localFilePath,
                'thumbnail_url' => null, // Poderia gerar thumbnail depois
                'status' => 'available',
                'is_active' => true,
                'last_sync' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vídeo enviado com sucesso',
                'video' => $video
            ]);

        } catch (\Exception $e) {
            return $e;
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar vídeo: ' . $e->getMessage()
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
            // Remove da API
            $response = Http::delete("{$this->apiBaseUrl}/videos/{$video->api_id}");
            
            // Remove localmente
            if ($video->cached && $video->file_path) {
                Storage::disk('public')->delete($video->file_path);
            }
            
            $video->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vídeo removido com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover vídeo: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getVideoStats()
    {
        $totalVideos = Video::count();
        $cachedVideos = Video::where('cached', true)->count();
        $totalSize = Video::where('cached', true)->sum('size');
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
        try {
            $response = Http::timeout(5)->get("{$this->apiBaseUrl}/health");
            return $response->successful() ? 'online' : 'offline';
        } catch (\Exception $e) {
            return 'offline';
        }
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
}