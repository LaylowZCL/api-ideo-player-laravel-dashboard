<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdGroup;
use App\Models\Client;
use App\Services\ActiveDirectoryService;
use App\Services\AdTargetingService;
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
            'event_type' => 'nullable|string', // Se for report de vídeo
            'hostname' => 'nullable|string',
            'username' => 'nullable|string',
            'groups' => 'nullable|array',
            'groups.*' => 'string',
        ]);

        $monitor = app('client.monitor');
        
        // Registrar heartbeat
        $result = $monitor->heartbeat($request->client_id, [
            'platform' => $request->platform,
            'app_version' => $request->app_version,
            'event_type' => $request->event_type
        ]);

        $clientId = mb_strtolower(trim($request->client_id));

        $client = Client::firstOrCreate(
            ['client_id' => $clientId],
            [
                'hostname' => $request->input('hostname'),
                'platform' => $request->platform,
                'version' => $request->app_version,
                'ip_address' => $request->ip(),
                'first_seen_at' => now(),
            ]
        );

        $client->update([
            'hostname' => $request->input('hostname') ?: $client->hostname,
            'platform' => $request->platform,
            'version' => $request->app_version,
            'ip_address' => $request->ip(),
            'last_seen_at' => now(),
        ]);

        if (config('ad.enabled') && config('ad.sync_client_groups')) {
            if (config('ad.group_source') === 'json') {
                $adGroups = [];
                $groupIds = app(AdTargetingService::class)->getGroupIdsForMachine($clientId);
                if (!empty($groupIds)) {
                    $adGroups = AdGroup::whereIn('id', $groupIds)->get()->map(function ($group) {
                        return [
                            'name' => $group->name,
                            'dn' => $group->dn,
                        ];
                    })->toArray();
                }
            } else {
                $adGroups = app(ActiveDirectoryService::class)->getGroupsForComputer($request->client_id);
            }
            if (count($adGroups) > 0) {
                $groupIds = [];
                foreach ($adGroups as $adGroup) {
                    if (empty($adGroup['name'])) {
                        continue;
                    }
                    $group = AdGroup::firstOrCreate(
                        ['name' => $adGroup['name']],
                        [
                            'dn' => $adGroup['dn'] ?? null,
                            'source' => 'ad',
                            'active' => true,
                        ]
                    );
                    $groupIds[] = $group->id;
                }
                if (count($groupIds) > 0) {
                    $client->adGroups()->sync($groupIds);
                }
            }
        }

        if (app()->environment('local') && is_array($request->input('groups'))) {
            $groupIds = [];
            foreach ($request->input('groups') as $groupName) {
                $groupName = trim($groupName);
                if ($groupName === '') {
                    continue;
                }
                $group = AdGroup::firstOrCreate(
                    ['name' => $groupName],
                    ['source' => 'client', 'active' => true]
                );
                $groupIds[] = $group->id;
            }
            if (count($groupIds) > 0) {
                $client->adGroups()->syncWithoutDetaching($groupIds);
            }
        }
        
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
