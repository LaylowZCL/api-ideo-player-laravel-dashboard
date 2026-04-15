<?php

namespace App\Http\Controllers;

use App\Exports\VideoReportsExport;
use App\Mail\VideoReportsExportMail;
use App\Models\VideoReport;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class VideoReportController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'video_id' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'group_by' => 'nullable|in:day,week,month',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $filters = $validator->validated();
        $stats = $this->buildStats($filters);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'video_id' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'platform' => 'nullable|string|max:50',
            'event_type' => 'nullable|string|max:50',
            'completed' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:5|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $filters = $validator->validated();
        $perPage = $filters['per_page'] ?? 25;
        $reports = $this->baseQuery($filters)
            ->orderByDesc('viewed_at')
            ->paginate($perPage);

        $reports->getCollection()->transform(fn (VideoReport $report) => $this->mapReportForApi($report));

        return response()->json([
            'success' => true,
            'reports' => $reports,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->validateExportFilters($request);

        if ($filters instanceof JsonResponse) {
            return $filters;
        }

        $filename = 'relatorios-video-' . now()->format('Ymd_His') . '.xlsx';
        $payload = $this->buildExportPayload($filters, $request->user()?->name);

        app(AuditLogService::class)->log('reports.export_excel', 'success', [
            'filters' => $filters,
            'rows' => count($payload['detailed_rows']),
        ]);

        return Excel::download(new VideoReportsExport($payload), $filename);
    }

    public function emailExcel(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'video_id' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'platform' => 'nullable|string|max:50',
            'event_type' => 'nullable|string|max:50',
            'completed' => 'nullable|boolean',
            'group_by' => 'nullable|in:day,week,month',
            'recipient_email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $recipientEmail = $data['recipient_email'];
        $recipientLabel = $recipientEmail;

        $payload = $this->buildExportPayload($data, $request->user()?->name);
        $filename = 'exports/relatorios-video-' . now()->format('Ymd_His') . '.xlsx';

        Excel::store(new VideoReportsExport($payload), $filename, 'local');

        Mail::to($recipientEmail)->send(new VideoReportsExportMail(
            $payload['summary'],
            storage_path('app/' . $filename),
            basename($filename),
            $recipientLabel
        ));

        app(AuditLogService::class)->log('reports.email_export', 'success', [
            'filters' => $data,
            'recipient_email' => $recipientEmail,
            'rows' => count($payload['detailed_rows']),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Relatório enviado com sucesso para {$recipientLabel}.",
        ]);
    }

    public function videoReports($videoId): JsonResponse
    {
        $reports = VideoReport::where('video_id', $videoId)
            ->orderByDesc('viewed_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'reports' => $reports,
        ]);
    }

    private function validateExportFilters(Request $request): array|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'video_id' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'platform' => 'nullable|string|max:50',
            'event_type' => 'nullable|string|max:50',
            'completed' => 'nullable|boolean',
            'group_by' => 'nullable|in:day,week,month',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $validator->errors(),
            ], 422);
        }

        return $validator->validated();
    }

    private function baseQuery(array $filters)
    {
        $query = VideoReport::query();

        if (!empty($filters['video_id'])) {
            $query->where('video_id', $filters['video_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('viewed_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('viewed_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['platform'])) {
            $query->where('platform', $filters['platform']);
        }

        if (!empty($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (array_key_exists('completed', $filters) && $filters['completed'] !== '' && $filters['completed'] !== null) {
            $query->where('completed', (bool) $filters['completed']);
        }

        return $query;
    }

    private function buildStats(array $filters): array
    {
        $query = $this->baseQuery($filters);
        $groupBy = $filters['group_by'] ?? 'day';
        $platformExpression = $this->platformGroupExpression();
        $topVideoTitleExpression = $this->topVideoTitleExpression();

        $stats = [
            'total_reports' => $query->count(),
            'unique_videos' => (clone $query)->distinct('video_id')->count('video_id'),
            'total_starts' => (clone $query)->where('event_type', 'playback_started')->count(),
            'total_completions' => (clone $query)->where('event_type', 'video_completed')->count(),
            'completion_rate' => 0,
            'avg_duration' => round((float) ((clone $query)->whereNotNull('playback_duration')->avg('playback_duration') ?? 0), 2),
            'by_platform' => (clone $query)
                ->selectRaw("{$platformExpression} as platform, COUNT(*) as count")
                ->groupBy(DB::raw($platformExpression))
                ->pluck('count', 'platform'),
            'event_breakdown' => (clone $query)
                ->selectRaw('event_type, COUNT(*) as count')
                ->groupBy('event_type')
                ->orderByDesc('count')
                ->pluck('count', 'event_type'),
            'top_videos' => (clone $query)
                ->selectRaw("video_id, {$topVideoTitleExpression} as video_title, COUNT(*) as count")
                ->groupBy('video_id', DB::raw($topVideoTitleExpression))
                ->orderByDesc('count')
                ->limit(8)
                ->get(),
            'timeline' => $this->buildTimeline(clone $query, $groupBy),
        ];

        if ($stats['total_starts'] > 0) {
            $stats['completion_rate'] = round(($stats['total_completions'] / $stats['total_starts']) * 100, 2);
        }

        $stats['recent_reports'] = (clone $query)
            ->orderByDesc('viewed_at')
            ->limit(10)
            ->get()
            ->map(fn (VideoReport $report) => $this->mapReportForApi($report));

        return $stats;
    }

    private function buildTimeline($query, string $groupBy)
    {
        $format = match ($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        // Detect database type and use appropriate function
        $dateFormatFunction = $this->getDateFormatFunction($format);

        return $query
            ->selectRaw($dateFormatFunction . ' as period, COUNT(*) as count')
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    private function getDateFormatFunction(string $format): string
    {
        $connection = DB::connection()->getDriverName();
        
        if ($connection === 'pgsql') {
            // PostgreSQL uses TO_CHAR with different format specifiers
            $pgFormat = match ($format) {
                '%Y-%u' => 'YYYY-"W"IW',  // Week format
                '%Y-%m' => 'YYYY-MM',       // Month format
                '%Y-%m-%d' => 'YYYY-MM-DD', // Day format
                default => 'YYYY-MM-DD',
            };
            return "TO_CHAR(viewed_at, '{$pgFormat}')";
        }
        
        // MySQL/MariaDB use DATE_FORMAT
        return "DATE_FORMAT(viewed_at, '{$format}')";
    }

    private function platformGroupExpression(): string
    {
        return "COALESCE(NULLIF(platform, ''), 'unknown')";
    }

    private function topVideoTitleExpression(): string
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            return "COALESCE(video_title, 'Vídeo ' || COALESCE(video_id::text, 'sem-id'))";
        }

        return "COALESCE(video_title, CONCAT('Vídeo ', COALESCE(video_id, 'sem-id')))";
    }

    private function buildExportPayload(array $filters, ?string $requestedBy = null): array
    {
        $stats = $this->buildStats($filters);
        $reports = $this->baseQuery($filters)
            ->orderByDesc('viewed_at')
            ->get();

        $detailedRows = $reports->map(function (VideoReport $report) {
            $totalDurationSeconds = $this->resolveTotalDuration($report);
            $actionMomentSeconds = $this->resolveActionMoment($report, $totalDurationSeconds);

            return [
                'ID do registo' => $report->id,
                'ID do vídeo' => $report->video_id,
                'Título do vídeo' => $report->video_title ?? ('Vídeo ' . $report->video_id),
                'Evento' => $this->formatEvent($report->event_type),
                'Código do evento' => $report->event_type,
                'Concluído' => $report->completed ? 'Sim' : 'Não',
                'Tipo de disparo' => $report->trigger_type ?? '-',
                'Plataforma' => $this->formatPlatform($report->platform),
                'Versão da aplicação' => $report->app_version ?? '-',
                'IP' => $report->ip_address ?? '-',
                'Sessão' => $report->session_id ?? '-',
                'Momento da acção no vídeo' => $this->formatDuration($actionMomentSeconds),
                'Momento da acção no vídeo (s)' => $actionMomentSeconds,
                'Duração total do vídeo' => $this->formatDuration($totalDurationSeconds),
                'Duração total do vídeo (s)' => $totalDurationSeconds,
                'Data/hora da visualização' => optional($report->viewed_at)->format('Y-m-d H:i:s'),
                'User agent' => $report->user_agent ?? '-',
                'Dados do evento (JSON)' => $report->event_data ? json_encode($report->event_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '-',
                'Criado em' => optional($report->created_at)->format('Y-m-d H:i:s'),
                'Actualizado em' => optional($report->updated_at)->format('Y-m-d H:i:s'),
            ];
        })->all();

        $platformRows = collect($stats['by_platform'] ?? [])->map(fn ($count, $platform) => [
            'Plataforma' => $this->formatPlatform((string) $platform),
            'Total de eventos' => $count,
        ])->values()->all();

        $eventRows = collect($stats['event_breakdown'] ?? [])->map(fn ($count, $event) => [
            'Evento' => $this->formatEvent((string) $event),
            'Código do evento' => $event,
            'Total' => $count,
        ])->values()->all();

        $timelineRows = collect($stats['timeline'] ?? [])->map(fn ($item) => [
            'Período' => $item->period,
            'Total de eventos' => $item->count,
        ])->values()->all();

        $topVideoRows = collect($stats['top_videos'] ?? [])->map(fn ($item) => [
            'ID do vídeo' => $item->video_id,
            'Título do vídeo' => $item->video_title,
            'Total de eventos' => $item->count,
        ])->values()->all();

        $summary = [
            ['Campo' => 'Gerado em', 'Valor' => now()->format('Y-m-d H:i:s')],
            ['Campo' => 'Gerado por', 'Valor' => $requestedBy ?: 'Sistema'],
            ['Campo' => 'Vídeo filtrado', 'Valor' => $filters['video_id'] ?? 'Todos'],
            ['Campo' => 'Data inicial', 'Valor' => $filters['start_date'] ?? 'Sem filtro'],
            ['Campo' => 'Data final', 'Valor' => $filters['end_date'] ?? 'Sem filtro'],
            ['Campo' => 'Plataforma', 'Valor' => isset($filters['platform']) ? $this->formatPlatform($filters['platform']) : 'Todas'],
            ['Campo' => 'Evento', 'Valor' => isset($filters['event_type']) ? $this->formatEvent($filters['event_type']) : 'Todos'],
            ['Campo' => 'Conclusão', 'Valor' => array_key_exists('completed', $filters) ? ((bool) $filters['completed'] ? 'Concluído' : 'Parcial') : 'Todos'],
            ['Campo' => 'Total de eventos', 'Valor' => $stats['total_reports'] ?? 0],
            ['Campo' => 'Vídeos únicos', 'Valor' => $stats['unique_videos'] ?? 0],
            ['Campo' => 'Inícios', 'Valor' => $stats['total_starts'] ?? 0],
            ['Campo' => 'Conclusões', 'Valor' => $stats['total_completions'] ?? 0],
            ['Campo' => 'Taxa de conclusão', 'Valor' => ($stats['completion_rate'] ?? 0) . '%'],
            ['Campo' => 'Duração média (s)', 'Valor' => $stats['avg_duration'] ?? 0],
            ['Campo' => 'Total de linhas exportadas', 'Valor' => count($detailedRows)],
        ];

        return [
            'summary' => $summary,
            'detailed_rows' => $detailedRows,
            'platform_rows' => $platformRows,
            'event_rows' => $eventRows,
            'timeline_rows' => $timelineRows,
            'top_video_rows' => $topVideoRows,
        ];
    }

    private function mapReportForApi(VideoReport $report): array
    {
        $totalDuration = $this->resolveTotalDuration($report);
        $actionMoment = $this->resolveActionMoment($report, $totalDuration);

        return [
            'id' => $report->id,
            'video_id' => $report->video_id,
            'video_title' => $report->video_title ?? ('Vídeo ' . $report->video_id),
            'event_type' => $report->event_type,
            'platform' => $report->platform ?? 'unknown',
            'viewed_at' => $report->viewed_at ? $report->viewed_at->toIso8601String() : null,
            'duration' => $actionMoment,
            'duration_label' => $this->formatDuration($actionMoment),
            'total_duration' => $totalDuration,
            'total_duration_label' => $this->formatDuration($totalDuration),
            'completed' => (bool) $report->completed,
            'session_id' => $report->session_id,
            'app_version' => $report->app_version,
            'ip_address' => $report->ip_address,
        ];
    }

    private function formatPlatform(?string $value): string
    {
        return match ($value) {
            'windows' => 'Windows',
            'mac' => 'macOS',
            'darwin' => 'macOS',
            'linux' => 'Linux',
            default => 'Desconhecida',
        };
    }

    private function formatEvent(?string $value): string
    {
        return match ($value) {
            'playback_started' => 'Início da reprodução',
            'playback_completed' => 'Reprodução concluída',
            'playback_25_percent' => 'Reprodução a 25 por cento',
            'playback_50_percent' => 'Reprodução a 50 por cento',
            'playback_75_percent' => 'Reprodução a 75 por cento',
            'video_completed' => 'Vídeo concluído',
            'video_loaded' => 'Vídeo carregado',
            'video_interrupted' => 'Vídeo interrompido',
            'popup_opened' => 'Popup aberto',
            'popup_minimized' => 'Popup minimizado',
            'autoplay_blocked' => 'Autoplay bloqueado',
            'autoplay_started' => 'Reprodução automática iniciada',
            'playback_paused' => 'Reprodução em pausa',
            'playback_resumed' => 'Reprodução retomada',
            'user_closed' => 'Fechado pelo utilizador',
            'window_loaded' => 'Janela carregada',
            default => $value ?: 'Evento',
        };
    }

    private function resolveTotalDuration(VideoReport $report): float
    {
        $eventData = is_array($report->event_data) ? $report->event_data : [];

        $candidates = [
            $eventData['video_duration'] ?? null,
            $eventData['duration'] ?? null,
            $eventData['durationSeconds'] ?? null,
            $eventData['playback_duration'] ?? null,
            $report->playback_duration,
        ];

        foreach ($candidates as $candidate) {
            if (is_numeric($candidate) && (float) $candidate > 0) {
                return (float) $candidate;
            }
        }

        return 0;
    }

    private function resolvePlaybackPosition(VideoReport $report): float
    {
        $eventData = is_array($report->event_data) ? $report->event_data : [];

        $candidates = [
            $eventData['playback_position'] ?? null,
            $eventData['position'] ?? null,
            $report->playback_position,
        ];

        foreach ($candidates as $candidate) {
            if (is_numeric($candidate) && (float) $candidate >= 0) {
                return (float) $candidate;
            }
        }

        return 0;
    }

    private function resolveActionMoment(VideoReport $report, float $totalDurationSeconds): float
    {
        $explicitPosition = $this->resolvePlaybackPosition($report);

        if ($explicitPosition > 0) {
            return $explicitPosition;
        }

        return match ($report->event_type) {
            'playback_25_percent' => round($totalDurationSeconds * 0.25, 2),
            'playback_50_percent' => round($totalDurationSeconds * 0.50, 2),
            'playback_75_percent' => round($totalDurationSeconds * 0.75, 2),
            'video_completed', 'playback_completed' => $totalDurationSeconds,
            default => 0,
        };
    }

    private function formatDuration(float|int|null $seconds): string
    {
        if (!$seconds || $seconds <= 0) {
            return '-';
        }

        $seconds = (int) round((float) $seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
        }

        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }
}
