<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $schedules = Schedule::all();
        return view('dashboard.schedules', compact('schedules'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'video' => 'required|string|max:255',
            'time' => 'required',
            'monitor' => 'required|string|in:Principal,Secundário,Todos',
            'days' => 'required|array|min:1',
            'days.*' => 'in:seg,ter,qua,qui,sex,sab,dom',
            'active' => 'boolean',
            'duration' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $schedule = Schedule::create([
            'title' => $request->title,
            'video' => $request->video,
            'time' => $request->time,
            'days' => $request->days,
            'monitor' => $request->monitor,
            'active' => $request->active ?? true,
            'duration' => $request->duration
        ]);

        return response()->json(['message' => 'Agendamento criado com sucesso', 'schedule' => $schedule], 201);
    }

    public function toggleStatus($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->active = !$schedule->active;
        $schedule->save();

        return response()->json(['message' => 'Status alterado com sucesso', 'active' => $schedule->active]);
    }

    public function duplicate($id)
    {
        $schedule = Schedule::findOrFail($id);
        $newSchedule = $schedule->replicate();
        $newSchedule->title = $schedule->title . ' (Cópia)';
        $newSchedule->active = false;
        $newSchedule->save();

        return response()->json(['message' => 'Agendamento duplicado com sucesso', 'schedule' => $newSchedule]);
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return response()->json(['message' => 'Agendamento removido com sucesso']);
    }
}
