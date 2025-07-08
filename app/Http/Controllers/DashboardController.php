<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Video;
use App\Models\Log;
use Illuminate\Http\Request;


class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $stats = [
            [
                'title' => 'Vídeos Agendados',
                'value' => Schedule::where('active', true)->count(),
                'description' => 'Próximos 7 dias',
                'icon' => 'bi-calendar3',
                'color' => 'text-info'
            ],
            [
                'title' => 'Vídeos em Cache',
                'value' => Video::where('cached', true)->count(),
                'description' => number_format(Video::where('cached', true)->sum('size'), 1) . ' GB total',
                'icon' => 'bi-camera-video',
                'color' => 'text-success'
            ],
            [
                'title' => 'Execuções Hoje',
                'value' => Log::where('status', 'success')->whereDate('created_at', today())->count(),
                'description' => '100% sucesso',
                'icon' => 'bi-play-circle',
                'color' => 'text-primary'
            ],
            [
                'title' => 'Próxima Execução',
                'value' => Schedule::where('active', true)->orderBy('time')->first()->time ?? 'N/A',
                'description' => 'Em breve',
                'icon' => 'bi-clock',
                'color' => 'text-warning'
            ]
        ];

        $recentLogs = Log::orderBy('created_at', 'desc')->take(4)->get();
        $upcomingSchedules = Schedule::where('active', true)->orderBy('time')->take(3)->get();

        if ($request->expectsJson()) {
            return response()->json([
                'stats' => $stats,
                'recentLogs' => $recentLogs,
                'upcomingSchedules' => $upcomingSchedules
            ]);
        }

        return view('dashboard.index', compact('stats', 'recentLogs', 'upcomingSchedules'));
    }
}
