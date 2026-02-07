<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientMonitorController extends Controller
{
    /**
     * Endpoint único para tudo
     * POST /api/client/ping
     */
    public function ping(Request $request)
    {
        // Validação mínima
        $request->validate([
            'client_id' => 'required|string',
            'app_version' => 'required|string',
            'platform' => 'required|string',
            'event_type' => 'nullable|string' // Se for report de vídeo
        ]);

        $monitor = app('client.monitor');
        
        // Registrar heartbeat
        $result = $monitor->heartbeat($request->client_id, [
            'platform' => $request->platform,
            'app_version' => $request->app_version,
            'event_type' => $request->event_type
        ]);
        
        return response()->json([
            'success' => true,
            'client_id' => $request->client_id,
            'online' => $result['online_count'],
            'is_new' => $result['is_new'],
            'server_time' => now()->toIso8601String()
        ]);
    }
    
    /**
     * Obter estatísticas
     * GET /api/client/stats
     */
    public function stats()
    {
        $monitor = app('client.monitor');
        $stats = $monitor->getStats();
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
    
    /**
     * Listar clientes online
     * GET /api/client/online
     */
    public function online()
    {
        $monitor = app('client.monitor');
        $clients = $monitor->getOnlineClients(5); // Últimos 5 minutos
        
        return response()->json([
            'success' => true,
            'count' => count($clients),
            'clients' => $clients
        ]);
    }
    
    /**
     * Dashboard admin simples
     * GET /admin/clients
     */
    public function dashboard()
    {
        $monitor = app('client.monitor');
        $stats = $monitor->getStats();
        $clients = $monitor->getOnlineClients(30); // Últimos 30 minutos
        
        return view('admin.clients-simple', [
            'stats' => $stats,
            'clients' => $clients
        ]);
    }
}