<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\VideoReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VideoReportController extends Controller
{
    /*/ Store a new video report
    public function store(Request $request)
    {
        // Validar entrada
        $validator = Validator::make($request->all(), [
            'video_id' => 'required|string',
            'video_title' => 'nullable|string|max:255',
            'event_type' => 'required|string|in:popup_opened,playback_started,playback_paused,playback_resumed,playback_completed,playback_25_percent,playback_50_percent,playback_75_percent,user_closed,window_loaded,video_loaded,autoplay_blocked,popup_minimized,video_completed',
            'playback_position' => 'nullable|numeric|min:0',
            'playback_duration' => 'nullable|numeric|min:0',
            'device_info' => 'nullable|array',
            'device_info.user_agent' => 'nullable|string',
            'device_info.platform' => 'nullable|string',
            'device_info.app_version' => 'nullable|string',
            'trigger_type' => 'nullable|string|in:scheduled,manual',
            'session_id' => 'nullable|string',
            'timestamp' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            Log::warning('Invalid video report data', [
                'errors' => $validator->errors()->toArray(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();
            
            // Extrair dados do dispositivo
            $deviceInfo = $data['device_info'] ?? [];
            
            // Criar o report
            $report = VideoReport::create([
                'video_id' => $data['video_id'],
                'video_title' => $data['video_title'] ?? null,
                'event_type' => $data['event_type'],
                'event_data' => $data, // Salvar todos os dados originais
                'playback_position' => $data['playback_position'] ?? 0,
                'playback_duration' => $data['playback_duration'] ?? 0,
                'user_agent' => $deviceInfo['user_agent'] ?? $request->userAgent(),
                'platform' => $deviceInfo['platform'] ?? $this->detectPlatform($request->userAgent()),
                'app_version' => $deviceInfo['app_version'] ?? '1.0.0',
                'ip_address' => $request->ip(),
                'session_id' => $data['session_id'] ?? $this->generateSessionId(),
                'trigger_type' => $data['trigger_type'] ?? 'scheduled',
                'completed' => $data['event_type'] === 'video_completed' || $data['event_type'] === 'playback_completed',
                'viewed_at' => isset($data['timestamp']) ? Carbon::parse($data['timestamp']) : now()
            ]);

            Log::info('Video report stored', [
                'report_id' => $report->id,
                'video_id' => $report->video_id,
                'event_type' => $report->event_type
            ]);

            // Se for evento de conclusão, atualizar estatísticas do vídeo
            if (in_array($data['event_type'], ['video_completed', 'playback_completed'])) {
                $this->updateVideoCompletionStats($data['video_id']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Video report stored successfully',
                'report_id' => $report->id
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error storing video report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }*/

    // Get video statistics
    public function stats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_id' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'group_by' => 'nullable|in:day,week,month'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        
        $query = VideoReport::query();
        
        // Filtrar por vídeo
        if (!empty($data['video_id'])) {
            $query->where('video_id', $data['video_id']);
        }
        
        // Filtrar por data
        if (!empty($data['start_date'])) {
            $query->whereDate('viewed_at', '>=', $data['start_date']);
        }
        
        if (!empty($data['end_date'])) {
            $query->whereDate('viewed_at', '<=', $data['end_date']);
        }
        
        // Obter estatísticas básicas
        $stats = [
            'total_reports' => $query->count(),
            'unique_videos' => $query->distinct('video_id')->count('video_id'),
            'total_starts' => $query->clone()->where('event_type', 'playback_started')->count(),
            'total_completions' => $query->clone()->where('event_type', 'video_completed')->count(),
            'completion_rate' => 0,
            'avg_duration' => $query->clone()->where('event_type', 'video_completed')->avg('playback_duration') ?? 0,
            'by_platform' => $query->clone()->selectRaw('platform, COUNT(*) as count')
                ->groupBy('platform')
                ->get()
                ->pluck('count', 'platform')
        ];
        
        // Calcular taxa de conclusão
        if ($stats['total_starts'] > 0) {
            $stats['completion_rate'] = round(($stats['total_completions'] / $stats['total_starts']) * 100, 2);
        }
        
        // Se agrupar por período
        if (!empty($data['group_by'])) {
            $format = match($data['group_by']) {
                'day' => '%Y-%m-%d',
                'week' => '%Y-%U',
                'month' => '%Y-%m',
                default => '%Y-%m-%d'
            };
            
            $stats['timeline'] = $query->clone()
                ->selectRaw("DATE_FORMAT(viewed_at, '{$format}') as period, COUNT(*) as count, 
                            SUM(CASE WHEN event_type = 'playback_started' THEN 1 ELSE 0 END) as starts,
                            SUM(CASE WHEN event_type = 'video_completed' THEN 1 ELSE 0 END) as completions")
                ->groupBy('period')
                ->orderBy('period')
                ->get();
        }
        
        // Últimos reports
        $stats['recent_reports'] = $query->clone()
            ->with('video')
            ->orderBy('viewed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'video_id' => $report->video_id,
                    'video_title' => $report->video_title,
                    'event_type' => $report->event_type,
                    'platform' => $report->platform,
                    'viewed_at' => $report->viewed_at->toIso8601String(),
                    'duration' => $report->playback_duration
                ];
            });

        $stats['event_breakdown'] = $query->clone()
            ->selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'event_type');

        $stats['top_videos'] = $query->clone()
            ->selectRaw('video_id, COALESCE(video_title, CONCAT("Vídeo ", video_id)) as video_title, COUNT(*) as count')
            ->groupBy('video_id', 'video_title')
            ->orderBy('count', 'desc')
            ->limit(8)
            ->get();
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video_id' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'platform' => 'nullable|string|max:50',
            'event_type' => 'nullable|string|max:50',
            'completed' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:5|max:200'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        $query = VideoReport::query();

        if (!empty($data['video_id'])) {
            $query->where('video_id', $data['video_id']);
        }

        if (!empty($data['start_date'])) {
            $query->whereDate('viewed_at', '>=', $data['start_date']);
        }

        if (!empty($data['end_date'])) {
            $query->whereDate('viewed_at', '<=', $data['end_date']);
        }

        if (!empty($data['platform'])) {
            $query->where('platform', $data['platform']);
        }

        if (!empty($data['event_type'])) {
            $query->where('event_type', $data['event_type']);
        }

        if (array_key_exists('completed', $data)) {
            $query->where('completed', (bool) $data['completed']);
        }

        $perPage = $data['per_page'] ?? 25;
        $reports = $query->orderBy('viewed_at', 'desc')->paginate($perPage);

        $reports->getCollection()->transform(function ($report) {
            return [
                'id' => $report->id,
                'video_id' => $report->video_id,
                'video_title' => $report->video_title ?? ('Vídeo ' . $report->video_id),
                'event_type' => $report->event_type,
                'platform' => $report->platform ?? 'unknown',
                'viewed_at' => $report->viewed_at ? $report->viewed_at->toIso8601String() : null,
                'duration' => $report->playback_duration,
                'completed' => (bool) $report->completed,
                'session_id' => $report->session_id,
                'app_version' => $report->app_version,
                'ip_address' => $report->ip_address
            ];
        });

        return response()->json([
            'success' => true,
            'reports' => $reports
        ]);
    }

    // Get reports for specific video
    public function videoReports($videoId)
    {
        $reports = VideoReport::where('video_id', $videoId)
            ->orderBy('viewed_at', 'desc')
            ->paginate(20);
            
        return response()->json([
            'success' => true,
            'reports' => $reports
        ]);
    }

    // Helper methods
    private function detectPlatform($userAgent)
    {
        if (stripos($userAgent, 'win') !== false) return 'windows';
        if (stripos($userAgent, 'mac') !== false) return 'mac';
        if (stripos($userAgent, 'linux') !== false) return 'linux';
        return 'unknown';
    }

    private function generateSessionId()
    {
        return md5(uniqid(mt_rand(), true) . microtime(true));
    }

    private function updateVideoCompletionStats($videoId)
    {
        // Aqui você pode atualizar estatísticas na tabela de vídeos
        // Por exemplo, incrementar um contador de visualizações completas
        // Se você tiver um modelo Video:
        
        // if ($video = \App\Models\Video::find($videoId)) {
        //     $video->increment('completed_views');
        //     $video->last_viewed_at = now();
        //     $video->save();
        // }
    }
}
