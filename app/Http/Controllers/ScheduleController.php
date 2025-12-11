<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
            ->map(function($schedule) {
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
        $schedules = Schedule::with('video')->get()->map(function($schedule) {
            return [
                'id' => $schedule->id,
                'title' => $schedule->title,
                'video_url' => $schedule->video ? $schedule->video->title : 'N/A',
                'video_id' => $schedule->video_id,
                'time' => $schedule->time,
                'days' => is_string($schedule->days) ? json_decode($schedule->days, true) : $schedule->days,
                'monitor' => $schedule->monitor,
                'active' => $schedule->active,
                'duration' => $schedule->video ? $schedule->video->duration : 'N/A'
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
            ->map(function($video) {
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
            'active' => 'boolean'
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

            $schedule = Schedule::create([
                'title' => $request->title,
                'video_url' => $video ? $video->url : $request->video_url,
                'video_id' => $video ? $video->id : null,
                'time' => $request->time,
                'days' => $request->days,
                'monitor' => $request->monitor,
                'active' => $request->active ?? true
            ]);

            // Carregar relacionamento para resposta
            $schedule->load('video');

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
                    'duration' => $schedule->video ? $schedule->video->duration : 'N/A'
                ]
            ]);

        } catch (\Exception $e) {
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

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'video_url' => 'sometimes|string',
            'time' => 'sometimes|date_format:H:i',
            'days' => 'sometimes|array',
            'days.*' => 'in:seg,ter,qua,qui,sex,sab,dom',
            'monitor' => 'sometimes|in:Principal,Secundário,Todos',
            'active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Atualizar vídeo se necessário
            if ($request->has('video_url')) {
                $video = Video::where('name', $request->video_url)
                    ->orWhere('url', $request->video_url)
                    ->orWhere('title', $request->video_url)
                    ->first();

                $schedule->video_url = $video ? $video->url : $request->video_url;
                $schedule->video_id = $video ? $video->id : null;
            }

            // Atualizar outros campos
            $updateData = $request->only(['title', 'time', 'days', 'monitor', 'active']);
            $schedule->update($updateData);

            $schedule->load('video');

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
                    'duration' => $schedule->video ? $schedule->video->duration : 'N/A'
                ]
            ]);

        } catch (\Exception $e) {
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

            return response()->json([
                'success' => true,
                'message' => 'Status do agendamento atualizado',
                'active' => $schedule->active
            ]);

        } catch (\Exception $e) {
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
                'video_id' => $original->video_id,
                'time' => $original->time,
                'days' => $original->days,
                'monitor' => $original->monitor,
                'active' => false
            ]);

            $schedule->load('video');

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
                    'duration' => $schedule->video ? $schedule->video->duration : 'N/A'
                ]
            ]);

        } catch (\Exception $e) {
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

            return response()->json([
                'success' => true,
                'message' => 'Agendamento removido com sucesso'
            ]);

        } catch (\Exception $e) {
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
            ->map(function($schedule) {
                return [
                    'id' => $schedule->id,
                    'title' => $schedule->title,
                    'time' => $schedule->time,
                    'video_url' => $schedule->video && $schedule->video->cached ? 
                        asset('storage/' . $schedule->video->file_path) : 
                        $schedule->video_url,
                    'video_title' => $schedule->video ? $schedule->video->title : 'Vídeo',
                    'duration' => $schedule->video ? $schedule->video->duration : '0:00',
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
}