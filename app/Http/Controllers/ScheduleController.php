<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function goToSchedule()
    {
        $schedules = Schedule::all();
        return view('schedule', compact('schedules'));
    }

    public function index()
    {
        $schedules = Schedule::with('video')->get()->map(function($schedule) {
            return [
                'id' => $schedule->id,
                'title' => $schedule->title,
                'video' => $schedule->video->name,
                'time' => $schedule->time,
                'days' => json_decode($schedule->days),
                'monitor' => $schedule->monitor,
                'active' => $schedule->active,
                'duration' => $schedule->video->duration
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
