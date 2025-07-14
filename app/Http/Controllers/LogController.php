<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function goToLogs()
    {
        $logs = Log::all();
        return view('logs', compact('logs'));
    }

    public function export()
    {
        $logs = Log::all();
        $csvContent = "Horário,Status,Evento,Detalhes,Nível\n";
        $csvContent .= $logs->map(function ($log) {
            return "{$log->time},{$log->status},{$log->event},{$log->video},{$log->level}";
        })->implode("\n");

        $filename = 'videoscheduler_logs_' . now()->toDateString() . '.csv';
        Storage::disk('public')->put($filename, $csvContent);

        return response()->download(storage_path("app/public/{$filename}"))->deleteFileAfterSend();
    }

    public function clear()
    {
        Log::truncate();
        return redirect()->route('logs.index')->with('success', 'Logs limpos com sucesso');
    }
}
