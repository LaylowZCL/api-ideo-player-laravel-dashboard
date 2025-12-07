<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        $validApiKey = config('app.api_key', 'VIDEO_POPUP_SECRET_2025');
        $clientId = config('app.client_id', 'ELECTRON_VIDEO_PLAYER');
        
        $apiKey = $request->header('X-API-Key');
        $client = $request->header('X-Client-ID');
        
        if (!$apiKey || !$client) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'error' => 'Missing authentication headers'
            ], 401);
        }
        
        if ($apiKey !== $validApiKey || $client !== $clientId) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error' => 'Invalid API key or client ID'
            ], 403);
        }
        
        return $next($request);
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
}