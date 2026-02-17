<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use App\Models\Schedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Models\VideoReport;
// use App\Models\Log;
use Illuminate\Support\Facades\Log;

class ClientAppController extends Controller
{

    public function schedules(): JsonResponse
    {
        // Mapeamento dos dias da semana de inglês para português
        $diasDaSemana = [
            'Sunday' => 'dom',
            'Monday' => 'seg',
            'Tuesday' => 'ter',
            'Wednesday' => 'qua',
            'Thursday' => 'qui',
            'Friday' => 'sex',
            'Saturday' => 'sab',
        ];

        // Obtém o nome do dia da semana atual em português
        $diaAtual = $diasDaSemana[now()->format('l')];

        // Obtém os horários agendados que estão ativos e no dia atual
        $schedules = Schedule::where('active', true)->whereJsonContains('days', $diaAtual)->get();

        // Extraindo os horários dos objetos Schedule
        $scheduleTimes = $schedules->pluck('time')->toArray();

        return response()->json([
            'schedule_times' => $scheduleTimes
        ]);
    }

    public function scheduledVideos(){
        $diasDaSemana = [
            'Sunday' => 'dom',
            'Monday' => 'seg',
            'Tuesday' => 'ter',
            'Wednesday' => 'qua',
            'Thursday' => 'qui',
            'Friday' => 'sex',
            'Saturday' => 'sab',
        ];

        $diaAtual = $diasDaSemana[now()->format('l')];
        $schedules = Schedule::where('active', true)
            ->whereJsonContains('days', $diaAtual)
            ->orderBy('time')
            ->get()
            ->map(function ($schedule) {
                $video = $this->resolveVideoForSchedule($schedule);
                $resolvedUrl = $video?->url ?: $schedule->video_url;

                return [
                    'id' => $schedule->id,
                    'title' => $schedule->title,
                    'video_id' => $video?->id,
                    'video_url' => $this->toSafeVideoUrl($resolvedUrl),
                    'time' => $schedule->time,
                    'days' => $schedule->days,
                    'monitor' => $schedule->monitor,
                    'active' => (bool) $schedule->active,
                    'duration' => $video?->duration ?: ($schedule->duration ?: '0:00'),
                    'created_at' => $schedule->created_at,
                    'updated_at' => $schedule->updated_at,
                ];
            });

        return response()->json([
            'videos' => $schedules
        ]);

    }

    public function storeReport(Request $request)
    {
        // Validar entrada
        $validator = Validator::make($request->all(), [
            'video_id' => 'nullable',
            'video_title' => 'nullable|string|max:255',
            'event_type' => 'string|in:popup_opened,playback_started,playback_paused,playback_resumed,video_completed,playback_completed,playback_error,user_closed,video_interrupted,autoplay_started,autoplay_blocked,window_closed_after_completion,window_loaded,video_loaded,playback_25_percent,playback_50_percent,playback_75_percent',
            'playback_position' => 'nullable|numeric|min:0',
            'playback_duration' => 'nullable|numeric|min:0',
            'video_duration' => 'nullable|numeric|min:0',
            'device_info' => 'nullable|array',
            'device_info.user_agent' => 'nullable|string',
            'device_info.platform' => 'nullable|string',
            'device_info.app_version' => 'nullable|string',
            'trigger_type' => 'nullable|string|in:scheduled,manual,activate,manual-reload',
            'session_id' => 'nullable|string',
            'timestamp' => 'nullable|date',
            'completion_status' => 'nullable|string|in:unknown,completed,interrupted',
            'interruption_reason' => 'nullable|string',
            'completed_loop' => 'nullable|boolean'
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
            $videoId = $data['video_id'] ?? null;
            if ($videoId && !Video::whereKey($videoId)->exists()) {
                $videoId = null;
            }

            $report = VideoReport::create([
                'video_id' => $videoId,
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

    private function resolveVideoForSchedule(Schedule $schedule): ?Video
    {
        if (Schema::hasColumn('schedules', 'video_id') && !empty($schedule->video_id)) {
            $video = Video::find($schedule->video_id);
            if ($video) {
                return $video;
            }
        }

        if (empty($schedule->video_url)) {
            return null;
        }

        $path = parse_url($schedule->video_url, PHP_URL_PATH) ?: $schedule->video_url;
        $basename = rawurldecode(basename($path));
        $withoutTimestampPrefix = preg_replace('/^\d+_/', '', $basename);

        return Video::query()
            ->where('name', $basename)
            ->orWhere('name', $withoutTimestampPrefix)
            ->orWhere('url', 'like', '%/' . rawurlencode($basename))
            ->orWhere('url', 'like', '%/' . rawurlencode($withoutTimestampPrefix))
            ->orderByDesc('cached')
            ->first();
    }

    private function toSafeVideoUrl(?string $url): ?string
    {
        if (empty($url)) {
            return $url;
        }

        $parts = parse_url($url);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return $url;
        }

        $segments = array_filter(explode('/', ltrim($parts['path'] ?? '', '/')), fn ($part) => $part !== '');
        $safeSegments = array_map(function ($segment) {
            return rawurlencode(rawurldecode($segment));
        }, $segments);

        $safePath = '/' . implode('/', $safeSegments);
        $safeUrl = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '') . $safePath;

        if (!empty($parts['query'])) {
            $safeUrl .= '?' . $parts['query'];
        }
        if (!empty($parts['fragment'])) {
            $safeUrl .= '#' . $parts['fragment'];
        }

        return $safeUrl;
    }
}
