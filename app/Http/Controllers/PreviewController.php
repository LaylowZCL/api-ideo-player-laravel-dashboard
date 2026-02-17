<?php

namespace App\Http\Controllers;

use App\Models\Preview;
use App\Models\Video;
use Illuminate\Http\Request;

class PreviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function goToPreview()
    {
        $previews = array(); //Preview::all();
        return view('previews', compact('previews'));
    }

    public function sync($id)
    {
        $preview = Preview::findOrFail($id);
        $preview->status = 'downloading';
        $preview->save();

        // Simular sincronização
        $preview->update([
            'cached' => true,
            'status' => 'synced',
            'last_sync' => now()
        ]);

        return response()->json(['message' => 'Vídeo sincronizado com sucesso', 'preview' => $preview]);
    }

    public function deleteFromCache($id)
    {
        $preview = Preview::findOrFail($id);
        $preview->update([
            'cached' => false,
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'Vídeo removido do cache com sucesso']);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'video_id' => 'required|exists:videos,id',
            'monitor' => 'required|in:primary,secondary,all',
            'width' => 'required|integer|min:100',
            'height' => 'required|integer|min:100',
            'always_on_top' => 'boolean',
            'auto_close' => 'boolean'
        ]);

        try {
            $video = Video::findOrFail($request->video_id);

            // Aqui você implementaria a lógica real para exibir no monitor
            // Esta é uma simulação:

            return response()->json([
                'success' => true,
                'message' => "Vídeo '{$video->title}' enviado para o monitor {$request->monitor}",
                'video' => $video
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exibir vídeo no monitor: ' . $e->getMessage()
            ], 500);
        }
    }
}
