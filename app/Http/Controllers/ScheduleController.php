<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ScheduleController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
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
        $schedules = Schedule::where('active', true)
            ->whereJsonContains('days', $diaAtual) // Verifica se 'days' contém o dia atual
            ->get();

        // Extraindo os horários dos objetos Schedule
        $scheduleTimes = $schedules->pluck('time')->toArray();

        return response()->json([
            'schedule_times' => $scheduleTimes
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
     * Cria um novo agendamento
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video_id' => 'required|exists:videos,id',
            'time' => 'required|date_format:H:i',
            'days' => 'required|array',
            'days.*' => 'in:seg,ter,qua,qui,sex,sab,dom',
            'monitor' => 'required|in:Principal,Secundário,Todos',
            'active' => 'boolean'
        ]);

        try {
            $schedule = Schedule::create([
                'title' => $request->title,
                'video_id' => $request->video_id,
                'time' => $request->time,
                'days' => json_encode($request->days),
                'monitor' => $request->monitor,
                'active' => $request->active ?? true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Agendamento criado com sucesso',
                'schedule' => $schedule
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar agendamento: ' . $e->getMessage()
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
                'video_id' => $original->video_id,
                'time' => $original->time,
                'days' => $original->days,
                'monitor' => $original->monitor,
                'active' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Agendamento duplicado com sucesso',
                'schedule' => $schedule
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
}
