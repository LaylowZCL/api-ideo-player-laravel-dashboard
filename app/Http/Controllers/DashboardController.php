<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Video;
use App\Models\Log;
use App\Models\VideoReport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['getDashboardData']);
    }

    /**
     * Rota principal - retorna a view
     */
    public function dashboard(Request $request)
    {
        return view('dashboard');
    }

    /**
     * API endpoint - retorna dados JSON
     */
    public function getDashboardData(Request $request)
    {
        // Verifica se a requisição vem do Vue (header personalizado)
        // OU se é uma requisição AJAX
        $isVueRequest = $request->header('X-Request-Source') === 'Vue-Component';
        $isAjax = $request->ajax() || $request->wantsJson();
        
        // Se não for Vue nem AJAX, bloqueia
        if (!$isVueRequest && !$isAjax) {
            return response()->json([
                'error' => 'Acesso restrito',
                'message' => 'Endpoint disponível apenas para a aplicação'
            ], 403);
        }

        try {
            // NOVOS CARDS baseados no VideoReport
            $totalViews = VideoReport::where('event_type', 'playback_started')->count();
            $totalCompletions = VideoReport::where('event_type', 'video_completed')->count();
            $uniqueSessions = VideoReport::distinct('session_id')->count('session_id');
            
            $completionRate = 0;
            if ($totalViews > 0) {
                $completionRate = round(($totalCompletions / $totalViews) * 100, 1);
            }

            $reportStats = [
                [
                    'title' => 'Total de Visualizações',
                    'value' => $totalViews,
                    'description' => 'Desde o início',
                    'icon' => 'bi-eye',
                    'color' => 'text-primary'
                ],
                [
                    'title' => 'Vídeos Concluídos',
                    'value' => $totalCompletions,
                    'description' => 'Assistidos até o fim',
                    'icon' => 'bi-check-circle',
                    'color' => 'text-success'
                ],
                [
                    'title' => 'Sessões Ativas',
                    'value' => $uniqueSessions,
                    'description' => 'Sessões únicas',
                    'icon' => 'bi-people',
                    'color' => 'text-info'
                ],
                [
                    'title' => 'Taxa de Conclusão',
                    'value' => $completionRate . '%',
                    'description' => 'De visualizações',
                    'icon' => 'bi-graph-up',
                    'color' => 'text-warning'
                ]
            ];

            // Seus cards originais
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
                    'description' => number_format(Video::where('cached', true)->sum('size') / 1073741824, 1) . ' GB total',
                    'icon' => 'bi-camera-video',
                    'color' => 'text-success'
                ],
                [
                    'title' => 'Vídeos Disponíveis',
                    'value' => Video::where('is_active', true)->count(),
                    'description' => 'Na API externa',
                    'icon' => 'bi-play-circle',
                    'color' => 'text-primary'
                ],
                [
                    'title' => 'Próxima Execução',
                    'value' => $this->getNextScheduleTime(),
                    'description' => $this->getTimeUntilNextSchedule(),
                    'icon' => 'bi-clock',
                    'color' => 'text-warning'
                ]
            ];

            // Estatísticas do dia
            $todayViews = VideoReport::whereDate('viewed_at', today())->count();
            $todayCompletions = VideoReport::whereDate('viewed_at', today())
                ->where('event_type', 'video_completed')
                ->count();
            
            // Vídeo mais visualizado
            $mostViewed = VideoReport::select('video_title', \DB::raw('COUNT(*) as views'))
                ->whereNotNull('video_title')
                ->groupBy('video_title')
                ->orderBy('views', 'desc')
                ->first();

            $viewStats = [
                'today_views' => $todayViews,
                'today_completions' => $todayCompletions,
                'avg_duration' => round(VideoReport::where('event_type', 'video_completed')->avg('playback_duration') ?? 0, 1),
                'top_video' => $mostViewed ? [
                    'title' => $mostViewed->video_title,
                    'views' => $mostViewed->views
                ] : null
            ];

            // Dados para gráficos
            $chartData = [
                'daily_views' => $this->getDailyViewsLast7Days(),
                'platform_distribution' => $this->getPlatformDistribution()
            ];

            // Logs recentes
            $recentLogs = Log::orderBy('created_at', 'desc')->take(4)->get()->map(function($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'details' => $log->details,
                    'created_at' => $log->created_at,
                    'status' => 'success'
                ];
            })->toArray();

            // Próximos agendamentos - usando o novo método
            $upcomingSchedules = $this->getUpcomingSchedulesForDashboard();

            // Relatórios recentes
            $recentReports = VideoReport::orderBy('viewed_at', 'desc')
                ->take(25)
                ->get()
                ->map(function($report) {
                    return [
                        'video_title' => $report->video_title ?? 'Vídeo ' . $report->video_id,
                        'event_type' => $this->translateEventType($report->event_type),
                        'platform' => $report->platform ?? 'Desconhecido',
                        'viewed_at' => $report->viewed_at ? $report->viewed_at->format('H:i') : 'N/A',
                        'duration' => $report->playback_duration ?? 0,
                        'completed' => (bool)$report->completed
                    ];
                })->toArray();

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'reportStats' => $reportStats,
                'viewStats' => $viewStats,
                'chartData' => $chartData,
                'recentLogs' => $recentLogs,
                'upcomingSchedules' => $upcomingSchedules,
                'recentReports' => $recentReports,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados do dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Encontra o horário agendado mais próximo do horário atual
     */
    private function getNextScheduleTime()
    {
        // Pega todos os agendamentos ativos
        $schedules = Schedule::where('active', true)
            ->orderBy('time')
            ->get();
        
        if ($schedules->isEmpty()) {
            return 'N/A';
        }
        
        $now = Carbon::now();
        $currentTime = $now->format('H:i');
        
        // Procura o próximo horário de hoje
        foreach ($schedules as $schedule) {
            if ($schedule->time >= $currentTime) {
                // Verifica se o agendamento é para hoje (baseado nos dias)
                $today = strtolower($now->locale('pt')->dayName);
                $shortDay = substr($today, 0, 3); // seg, ter, qua, etc
                
                $days = is_array($schedule->days) ? $schedule->days : json_decode($schedule->days, true);
                
                if (is_array($days) && in_array($shortDay, $days)) {
                    return $schedule->time;
                }
            }
        }
        
        // Se não encontrou para hoje, procura o primeiro de amanhã
        $tomorrow = $now->copy()->addDay();
        $tomorrowDay = strtolower($tomorrow->locale('pt')->dayName);
        $shortTomorrowDay = substr($tomorrowDay, 0, 3);
        
        foreach ($schedules as $schedule) {
            $days = is_array($schedule->days) ? $schedule->days : json_decode($schedule->days, true);
            
            if (is_array($days) && in_array($shortTomorrowDay, $days)) {
                return $schedule->time;
            }
        }
        
        // Se não encontrou, retorna o primeiro horário disponível
        return $schedules->first()->time ?? 'N/A';
    }

    /**
     * Calcula quanto tempo falta para a próxima execução
     */
    private function getTimeUntilNextSchedule()
    {
        $nextTime = $this->getNextScheduleTime();
        
        if ($nextTime === 'N/A') {
            return 'Sem agendamentos';
        }
        
        $now = Carbon::now();
        
        // Cria um objeto Carbon com o horário do agendamento
        $scheduleTime = Carbon::createFromTime(
            substr($nextTime, 0, 2), // hora
            substr($nextTime, 3, 2)  // minuto
        );
        
        // Se o horário já passou hoje, assume que é para amanhã
        if ($scheduleTime->lt($now)) {
            $scheduleTime->addDay();
        }
        
        // Calcula a diferença
        $diff = $now->diff($scheduleTime);
        
        if ($diff->d > 0) {
            return $diff->d . ' dia' . ($diff->d > 1 ? 's' : '') . ' e ' . $diff->h . 'h';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hora' . ($diff->h > 1 ? 's' : '') . ' e ' . $diff->i . 'min';
        } elseif ($diff->i > 0) {
            return $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '');
        } else {
            return 'Agora';
        }
    }

    /**
     * Obtém visualizações diárias dos últimos 7 dias
     */
    private function getDailyViewsLast7Days()
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        
        $data = VideoReport::where('viewed_at', '>=', $startDate)
            ->selectRaw('DATE(viewed_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        $dailyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateString = $date->format('Y-m-d');
            $dayName = $date->locale('pt')->dayName;
            $shortDayName = substr($dayName, 0, 3);
            
            $dailyData[] = [
                'date' => $shortDayName,
                'full_date' => $date->format('d/m'),
                'views' => isset($data[$dateString]) ? $data[$dateString]->count : 0
            ];
        }

        return $dailyData;
    }

    /**
     * Distribuição por plataforma
     */
    private function getPlatformDistribution()
    {
        return VideoReport::selectRaw('platform, COUNT(*) as count')
            ->whereNotNull('platform')
            ->groupBy('platform')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get()
            ->map(function($item) {
                return [
                    'platform' => $item->platform ?: 'Desconhecido',
                    'count' => $item->count
                ];
            })->toArray();
    }

    /**
     * Traduz tipos de evento para português
     */
    private function translateEventType($eventType)
    {
        $translations = [
            'popup_opened' => 'Popup Aberto',
            'playback_started' => 'Reprodução Iniciada',
            'playback_paused' => 'Pausado',
            'playback_resumed' => 'Retomado',
            'playback_completed' => 'Concluído',
            'video_completed' => 'Vídeo Concluído',
            'user_closed' => 'Usuário Fechou',
            'window_loaded' => 'Janela Carregada',
            'video_loaded' => 'Vídeo Carregado',
            'autoplay_blocked' => 'Auto-play Bloqueado',
            'popup_minimized' => 'Popup Minimizado'
        ];

        return $translations[$eventType] ?? $eventType;
    }

    /**
     * Obtém agendamentos próximos para o dashboard
     */
    private function getUpcomingSchedulesForDashboard()
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i');
        $today = strtolower($now->locale('pt')->dayName);
        $shortToday = substr($today, 0, 3);
        
        // Pega agendamentos de hoje e amanhã
        $schedules = Schedule::with('video')
            ->where('active', true)
            ->orderBy('time')
            ->get()
            ->filter(function($schedule) use ($currentTime, $shortToday, $now) {
                $days = is_array($schedule->days) ? $schedule->days : json_decode($schedule->days, true);
                
                if (!is_array($days)) {
                    return false;
                }
                
                // Verifica se é para hoje e ainda não passou
                if (in_array($shortToday, $days)) {
                    return $schedule->time >= $currentTime;
                }
                
                // Verifica se é para amanhã
                $tomorrow = $now->copy()->addDay();
                $tomorrowDay = strtolower($tomorrow->locale('pt')->dayName);
                $shortTomorrowDay = substr($tomorrowDay, 0, 3);
                
                return in_array($shortTomorrowDay, $days);
            })
            ->take(3)
            ->map(function($schedule) use ($shortToday, $now) {
                $days = is_array($schedule->days) ? $schedule->days : json_decode($schedule->days, true);
                $today = strtolower($now->locale('pt')->dayName);
                $shortToday = substr($today, 0, 3);
                
                $isToday = in_array($shortToday, $days);
                
                return [
                    'id' => $schedule->id,
                    'title' => $schedule->title,
                    'time' => $schedule->time,
                    'monitor' => $schedule->monitor,
                    'is_today' => $isToday,
                    'time_until' => $this->calculateTimeUntil($schedule->time, $isToday),
                    'video' => $schedule->video ? [
                        'duration' => $schedule->video->duration,
                        'title' => $schedule->video->title,
                        'cached' => $schedule->video->cached
                    ] : null
                ];
            })->values()->toArray();
        
        return $schedules;
    }

    /**
     * Calcula quanto tempo falta para um agendamento específico
     */
    private function calculateTimeUntil($scheduleTime, $isToday = true)
    {
        $now = Carbon::now();
        
        // Cria um objeto Carbon com o horário do agendamento
        $time = Carbon::createFromTime(
            substr($scheduleTime, 0, 2),
            substr($scheduleTime, 3, 2)
        );
        
        // Se não for para hoje, adiciona um dia
        if (!$isToday) {
            $time->addDay();
        }
        
        // Se o horário já passou, assume que é para o próximo dia disponível
        if ($time->lt($now)) {
            $time->addDay();
        }
        
        $diff = $now->diff($time);
        
        if ($diff->d > 0) {
            return $diff->d . 'd ' . $diff->h . 'h';
        } elseif ($diff->h > 0) {
            return $diff->h . 'h ' . $diff->i . 'm';
        } else {
            return $diff->i . 'min';
        }
    }
}