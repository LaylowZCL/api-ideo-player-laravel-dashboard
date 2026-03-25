<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Video;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ScheduleController extends Controller
{
    public function __construct()
    {
        // Apenas a view precisa de autenticação web
        $this->middleware('auth')->only(['goToSchedule']);

        // Todos os métodos API usam verificação interna
        $this->middleware('internal.api')->except(['goToSchedule']);
    }

    public function goToSchedule()
    {
        $schedules = Schedule::all();
        return view('schedule', compact('schedules'));
    }

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

    /**
     * Obtém vídeos agendados para hoje
     */
    public function scheduledVideosToday()
    {
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

        $schedules = Schedule::with('video')
            ->where('active', true)
            ->whereJsonContains('days', $diaAtual)
            ->orderBy('time')
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'title' => $schedule->title,
                    'time' => $schedule->time,
                    'video_url' => $schedule->video_url,
                    'video' => $schedule->video ? [
                        'title' => $schedule->video->title,
                        'duration' => $schedule->video->duration,
                        'cached' => $schedule->video->cached,
                        'file_path' => $schedule->video->file_path
                    ] : null,
                    'monitor' => $schedule->monitor
                ];
            });

        return response()->json([
            'videos' => $schedules,
            'today' => now()->format('d/m/Y'),
            'day_name' => Carbon::now()->locale('pt')->dayName
        ]);
    }

    public function index()
    {
        $schedules = Schedule::with(['video', 'campaign', 'targetGroups', 'targetClients'])->get()->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'title' => $schedule->title,
                'video_url' => $schedule->video ? $schedule->video->title : 'N/A',
                'video_id' => $schedule->video_id,
                'time' => $schedule->time,
                'days' => is_string($schedule->days) ? json_decode($schedule->days, true) : $schedule->days,
                'monitor' => $schedule->monitor,
                'active' => $schedule->active,
                'duration' => $this->resolveScheduleDuration($schedule),
                'priority' => $schedule->priority ?? 0,
                'campaign_id' => $schedule->campaign_id,
                'campaign' => $schedule->campaign ? [
                    'id' => $schedule->campaign->id,
                    'name' => $schedule->campaign->name,
                ] : null,
                'target_groups' => $schedule->targetGroups->pluck('id')->all(),
                'target_clients' => $schedule->targetClients->pluck('id')->all(),
            ];
        });

        return response()->json($schedules);
    }

    /**
     * Obtém todos os vídeos para o dropdown
     */
    public function getVideosForDropdown()
    {
        $videos = Video::where('is_active', true)
            ->select('id', 'title', 'name', 'duration', 'cached', 'url')
            ->orderBy('title')
            ->get()
            ->map(function ($video) {
                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'name' => $video->name,
                    'duration' => $video->duration,
                    'cached' => $video->cached,
                    'url' => $video->url
                ];
            });

        return response()->json([
            'videos' => $videos
        ]);
    }

    /**
     * Cria um novo agendamento
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'video_url' => 'required|string',
            'time' => 'required|date_format:H:i',
            'days' => 'required|array',
            'days.*' => 'in:seg,ter,qua,qui,sex,sab,dom',
            'monitor' => 'required|in:Principal,Secundário,Todos',
            'active' => 'boolean',
            'subtitle_url' => 'nullable|string|max:500',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'priority' => 'nullable|integer|min:0|max:100',
            'target_groups' => 'nullable|array',
            'target_groups.*' => 'integer|exists:ad_groups,id',
            'target_clients' => 'nullable|array',
            'target_clients.*' => 'integer|exists:clients,id',
            'window_config' => 'nullable|array',
            'window_config.position' => 'nullable|array',
            'window_config.position.anchor' => 'nullable|in:top-left,top-right,bottom-left,bottom-right,center,top-center,bottom-center',
            'window_config.position.x' => 'nullable|integer|min:0',
            'window_config.position.y' => 'nullable|integer|min:0',
            'window_config.position.margin' => 'nullable|integer|min:0|max:100',
            'window_config.size' => 'nullable|array',
            'window_config.size.width' => 'nullable|integer|min:320|max:3840',
            'window_config.size.height' => 'nullable|integer|min:180|max:2160',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Tenta encontrar o vídeo pelo nome ou URL
            $video = Video::where('name', $request->video_url)
                ->orWhere('url', $request->video_url)
                ->orWhere('title', $request->video_url)
                ->first();

            $scheduleData = [
                'title' => $request->title,
                'video_url' => $video ? $video->url : $request->video_url,
                'time' => $request->time,
                'days' => $request->days,
                'monitor' => $request->monitor,
                'active' => $request->active ?? true,
                'duration' => $video ? $video->duration : '0:00',
                'subtitle_url' => $request->input('subtitle_url'),
                'window_config' => $request->has('window_config') ? $request->input('window_config') : null,
                'campaign_id' => $request->input('campaign_id'),
                'priority' => $request->input('priority', 0),
            ];

            if ($this->hasVideoIdColumn()) {
                $scheduleData['video_id'] = $video ? $video->id : null;
            }

            $schedule = Schedule::create($scheduleData);

            $targetGroups = $request->input('target_groups', []);
            $targetClients = $request->input('target_clients', []);

            if (is_array($targetGroups)) {
                $schedule->targetGroups()->sync($targetGroups);
            }
            if (is_array($targetClients)) {
                $schedule->targetClients()->sync($targetClients);
            }

            $schedule->load(['video', 'campaign', 'targetGroups', 'targetClients']);

            app(AuditLogService::class)->log('schedule.create', 'success', [
                'schedule_id' => $schedule->id,
                'title' => $schedule->title,
                'campaign_id' => $schedule->campaign_id,
                'priority' => $schedule->priority ?? 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Agendamento criado com sucesso',
                'schedule' => [
                    'id' => $schedule->id,
                    'title' => $schedule->title,
                    'video_url' => $schedule->video ? $schedule->video->title : $schedule->video_url,
                    'video_id' => $schedule->video_id,
                    'time' => $schedule->time,
                    'days' => $schedule->days,
                    'monitor' => $schedule->monitor,
                    'active' => $schedule->active,
                    'duration' => $this->resolveScheduleDuration($schedule),
                    'subtitle_url' => $schedule->subtitle_url,
                    'window_config' => $schedule->window_config,
                    'priority' => $schedule->priority ?? 0,
                    'campaign_id' => $schedule->campaign_id,
                    'target_groups' => $schedule->targetGroups->pluck('id')->all(),
                    'target_clients' => $schedule->targetClients->pluck('id')->all(),
                ]
            ]);
        } catch (\Exception $e) {
            app(AuditLogService::class)->log('schedule.create', 'failed', [
                'error' => $e->getMessage(),
            ], 'error');
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar agendamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualiza um agendamento existente
     */
    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);

        $payload = $request->all();

        if (array_key_exists('target_groups', $payload) && is_array($payload['target_groups'])) {
            $payload['target_groups'] = array_values(array_map('intval', $payload['target_groups']));
        }

        if (array_key_exists('target_clients', $payload) && is_array($payload['target_clients'])) {
            $payload['target_clients'] = array_values(array_map('intval', $payload['target_clients']));
        }

        $validator = Validator::make($payload, [
            'title' => 'sometimes|string|max:255',
            'video_url' => 'sometimes|string',
            'time' => 'sometimes|date_format:H:i',
            'days' => 'sometimes|array',
            'days.*' => 'in:seg,ter,qua,qui,sex,sab,dom',
            'monitor' => 'sometimes|in:Principal,Secundário,Todos',
            'active' => 'sometimes|boolean',
            'subtitle_url' => 'nullable|string|max:500',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'priority' => 'nullable|integer|min:0|max:100',
            'target_groups' => 'nullable|array',
            'target_groups.*' => 'integer|exists:ad_groups,id',
            'target_clients' => 'nullable|array',
            'target_clients.*' => 'integer|exists:clients,id',
            'window_config' => 'nullable|array',
            'window_config.position' => 'nullable|array',
            'window_config.position.anchor' => 'nullable|in:top-left,top-right,bottom-left,bottom-right,center,top-center,bottom-center',
            'window_config.position.x' => 'nullable|integer|min:0',
            'window_config.position.y' => 'nullable|integer|min:0',
            'window_config.position.margin' => 'nullable|integer|min:0|max:100',
            'window_config.size' => 'nullable|array',
            'window_config.size.width' => 'nullable|integer|min:320|max:3840',
            'window_config.size.height' => 'nullable|integer|min:180|max:2160',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = $request->only(['title', 'time', 'days', 'monitor', 'active', 'subtitle_url', 'window_config', 'campaign_id', 'priority']);

            // Atualizar vídeo se necessário
            if ($request->has('video_url')) {
                $video = Video::where('name', $request->video_url)
                    ->orWhere('url', $request->video_url)
                    ->orWhere('title', $request->video_url)
                    ->first();

                $updateData['video_url'] = $video ? $video->url : $request->video_url;
                $updateData['duration'] = $video ? $video->duration : '0:00';

                if ($this->hasVideoIdColumn()) {
                    $updateData['video_id'] = $video ? $video->id : null;
                }
            }

            $schedule->update($updateData);

            if ($request->has('target_groups')) {
                $schedule->targetGroups()->sync($request->input('target_groups', []));
            }
            if ($request->has('target_clients')) {
                $schedule->targetClients()->sync($request->input('target_clients', []));
            }

            $schedule->load(['video', 'campaign', 'targetGroups', 'targetClients']);

            app(AuditLogService::class)->log('schedule.update', 'success', [
                'schedule_id' => $schedule->id,
                'title' => $schedule->title,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Agendamento atualizado com sucesso',
                'schedule' => [
                    'id' => $schedule->id,
                    'title' => $schedule->title,
                    'video_url' => $schedule->video ? $schedule->video->title : $schedule->video_url,
                    'video_id' => $schedule->video_id,
                    'time' => $schedule->time,
                    'days' => $schedule->days,
                    'monitor' => $schedule->monitor,
                    'active' => $schedule->active,
                    'duration' => $this->resolveScheduleDuration($schedule),
                    'subtitle_url' => $schedule->subtitle_url,
                    'window_config' => $schedule->window_config,
                    'priority' => $schedule->priority ?? 0,
                    'campaign_id' => $schedule->campaign_id,
                    'target_groups' => $schedule->targetGroups->pluck('id')->all(),
                    'target_clients' => $schedule->targetClients->pluck('id')->all(),
                ]
            ]);
        } catch (\Exception $e) {
            app(AuditLogService::class)->log('schedule.update', 'failed', [
                'schedule_id' => $schedule->id,
                'error' => $e->getMessage(),
            ], 'error');
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar agendamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alterna o status de um agendamento
     */
    public function toggleStatus($id)
    {
        $schedule = Schedule::findOrFail($id);

        try {
            $schedule->update([
                'active' => !$schedule->active
            ]);

            app(AuditLogService::class)->log('schedule.toggle', 'success', [
                'schedule_id' => $schedule->id,
                'active' => $schedule->active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status do agendamento atualizado',
                'active' => $schedule->active
            ]);
        } catch (\Exception $e) {
            app(AuditLogService::class)->log('schedule.toggle', 'failed', [
                'schedule_id' => $schedule->id,
                'error' => $e->getMessage(),
            ], 'error');
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplica um agendamento
     */
    public function duplicate($id)
    {
        $original = Schedule::findOrFail($id);

        try {
            $schedule = Schedule::create([
                'title' => $original->title . ' (Cópia)',
                'video_url' => $original->video_url,
                'time' => $original->time,
                'days' => $original->days,
                'monitor' => $original->monitor,
                'active' => false,
                'duration' => $original->duration ?? ($original->video ? $original->video->duration : '0:00'),
                'campaign_id' => $original->campaign_id,
                'priority' => $original->priority ?? 0,
            ] + ($this->hasVideoIdColumn() ? ['video_id' => $original->video_id] : []));

            $schedule->targetGroups()->sync($original->targetGroups->pluck('id')->all());
            $schedule->targetClients()->sync($original->targetClients->pluck('id')->all());

            $schedule->load(['video', 'campaign', 'targetGroups', 'targetClients']);

            app(AuditLogService::class)->log('schedule.duplicate', 'success', [
                'schedule_id' => $schedule->id,
                'original_id' => $original->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Agendamento duplicado com sucesso',
                'schedule' => [
                    'id' => $schedule->id,
                    'title' => $schedule->title,
                    'video_url' => $schedule->video ? $schedule->video->title : $schedule->video_url,
                    'video_id' => $schedule->video_id,
                    'time' => $schedule->time,
                    'days' => $schedule->days,
                    'monitor' => $schedule->monitor,
                    'active' => $schedule->active,
                    'duration' => $this->resolveScheduleDuration($schedule),
                    'priority' => $schedule->priority ?? 0,
                    'campaign_id' => $schedule->campaign_id,
                    'target_groups' => $schedule->targetGroups->pluck('id')->all(),
                    'target_clients' => $schedule->targetClients->pluck('id')->all(),
                ]
            ]);
        } catch (\Exception $e) {
            app(AuditLogService::class)->log('schedule.duplicate', 'failed', [
                'schedule_id' => $original->id,
                'error' => $e->getMessage(),
            ], 'error');
            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar agendamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove um agendamento
     */
    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);

        try {
            $schedule->delete();

            app(AuditLogService::class)->log('schedule.delete', 'success', [
                'schedule_id' => $schedule->id,
                'title' => $schedule->title,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Agendamento removido com sucesso'
            ]);
        } catch (\Exception $e) {
            app(AuditLogService::class)->log('schedule.delete', 'failed', [
                'schedule_id' => $schedule->id,
                'error' => $e->getMessage(),
            ], 'error');
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover agendamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtém agendamentos para um player externo
     */
    public function getScheduleForPlayer()
    {
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
        $currentTime = now()->format('H:i');

        $schedules = Schedule::with('video')
            ->where('active', true)
            ->whereJsonContains('days', $diaAtual)
            ->where('time', '>=', $currentTime)
            ->orderBy('time')
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'title' => $schedule->title,
                    'time' => $schedule->time,
                    'video_url' => $schedule->video && $schedule->video->cached ?
                        asset('storage/' . $schedule->video->file_path) :
                        $schedule->video_url,
                    'video_title' => $schedule->video ? $schedule->video->title : 'Vídeo',
                    'duration' => $this->resolveScheduleDuration($schedule),
                    'monitor' => $schedule->monitor,
                    'is_cached' => $schedule->video ? $schedule->video->cached : false
                ];
            });

        return response()->json([
            'success' => true,
            'schedules' => $schedules,
            'current_time' => $currentTime,
            'today' => now()->format('d/m/Y')
        ]);
    }

    private function hasVideoIdColumn(): bool
    {
        return Schema::hasColumn('schedules', 'video_id');
    }

    private function resolveScheduleDuration(Schedule $schedule): string
    {
        if ($schedule->video && !empty($schedule->video->duration)) {
            return $schedule->video->duration;
        }

        if (!empty($schedule->duration)) {
            return $schedule->duration;
        }

        return '0:00';
    }
}
