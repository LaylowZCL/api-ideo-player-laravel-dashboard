<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $videos = Video::all();
        return view('dashboard.videos', compact('videos'));
    }

    public function sync($id)
    {
        $video = Video::findOrFail($id);
        $video->status = 'downloading';
        $video->save();

        // Simular sincronização
        $video->update([
            'cached' => true,
            'status' => 'synced',
            'last_sync' => now()
        ]);

        return response()->json(['message' => 'Vídeo sincronizado com sucesso', 'video' => $video]);
    }

    public function deleteFromCache($id)
    {
        $video = Video::findOrFail($id);
        $video->update([
            'cached' => false,
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'Vídeo removido do cache com sucesso']);
    }
}
