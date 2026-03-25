<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class LogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Gate::allows('isAdmin')) {
                abort(403, 'Acesso restrito a administradores.');
            }
            return $next($request);
        });
    }

    public function goToLogs()
    {
        $logs = Log::all();
        app(AuditLogService::class)->log('logs.view', 'success');
        return view('logs', compact('logs'));
    }

    public function indexJson(Request $request)
    {
        $query = Log::query();

        if ($request->filled('level')) {
            $query->where('level', $request->string('level'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                    ->orWhere('video', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderByDesc('id')->paginate(50);

        return response()->json([
            'logs' => $logs->items(),
            'total' => $logs->total(),
            'page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
        ]);
    }

    public function export()
    {
        $logs = Log::all();
        app(AuditLogService::class)->log('logs.export', 'success', ['count' => $logs->count()]);
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
        $count = Log::count();
        Log::truncate();
        app(AuditLogService::class)->log('logs.clear', 'success', ['count' => $count]);
        return redirect()->route('logs.index')->with('success', 'Logs limpos com sucesso');
    }

    public function clearJson()
    {
        $count = Log::count();
        Log::truncate();
        app(AuditLogService::class)->log('logs.clear', 'success', ['count' => $count]);

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }
}
