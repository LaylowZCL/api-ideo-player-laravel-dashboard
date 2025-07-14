<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class VideoController extends Controller
{
    public function __construct()
    {
       // $this->middleware('auth');
    }

    public function goToVideos()
    {
        $videos = Video::all();
        return view('videos', compact('videos'));
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
                    'title' => $video->title,
                    'name' => $video->name,
                    'duration' => $video->duration,
                    'size' => $this->formatSize($video->size),
                    'status' => $video->status,
                    'cached' => $video->cached,
                    'lastSync' => $video->last_sync ? Carbon::parse($video->last_sync)->format('d/m/Y H:i') : null,
                    'path' => $video->path,
                ];
            });

        return response()->json([
            'videos' => $videos,
            'stats' => $this->getVideoStats()
        ]);
    }

    public function sync(Request $request)
    {
        try {
            // Simula tempo de sincronização
            sleep(2);

            // Atualiza os vídeos no banco de dados
            $updatedCount = Video::where('status', '!=', 'synced')->update([
                'status' => 'synced',
                'last_sync' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sincronização concluída com sucesso',
                'updated' => $updatedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao sincronizar vídeos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download($id)
    {
        $video = Video::findOrFail($id);

        try {
            // Simula o download (em produção, você faria o download real)
            $video->update([
                'status' => 'downloading',
                'cached' => false
            ]);

            // Simula tempo de download
            sleep(3);

            $video->update([
                'status' => 'synced',
                'cached' => true,
                'last_sync' => now(),
                'path' => 'videos/' . $video->name
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

    public function removeFromCache($id)
    {
        $video = Video::findOrFail($id);

        try {
            if ($video->cached && Storage::exists($video->path)) {
                Storage::delete($video->path);
            }

            $video->update([
                'cached' => false,
                'status' => 'pending',
                'path' => null
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

    public function upload(Request $request)
    {

        $request->validate([
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime|max:102400',
            'title' => 'required|string|max:255'
        ]);

        try {
            $file = $request->file('video');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            // Armazena no disco público
            $path = $file->storeAs('videos', $filename, 'public');

            // Obtém a URL base dinamicamente
            $baseUrl = URL::to('/');

            // Remove a barra final se existir para evitar duplicação
            $baseUrl = rtrim($baseUrl, '/');

            // Cria a URL completa do vídeo
            $videoUrl = $baseUrl . '/storage/videos/' . $filename;

            $video = Video::create([
                'title' => $request->title,
                'name' => $filename,
                'duration' => $this->getVideoDuration($file),
                'size' => $file->getSize(),
                'status' => 'synced',
                'cached' => true,
                'path' => $path,
                'url' => $videoUrl, // URL dinâmica baseada no root da aplicação
                'last_sync' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vídeo enviado com sucesso',
                'video' => $video
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar vídeo: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getVideoStats()
    {
        $totalVideos = Video::count();
        $cachedVideos = Video::where('cached', true)->count();
        $totalSize = Video::where('cached', true)->sum('size');

        return [
            'total_videos' => $totalVideos,
            'cached_videos' => $cachedVideos,
            'total_size' => $this->formatSize($totalSize),
            'api_status' => 'online' // Você pode verificar o status real da API aqui
        ];
    }

    /**
     * Formata o tamanho em bytes para GB/MB
     */
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

    /**
     * Obtém a duração do vídeo (simplificado)
     */
    private function getVideoDuration($file)
    {
        // Em produção, use uma biblioteca como ffmpeg para obter a duração real
        return '0:00'; // Simulação
    }
}
