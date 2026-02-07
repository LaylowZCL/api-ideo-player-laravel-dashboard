<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;

class ClientMonitorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('client.monitor', function () {
            return new class {
                private $clients = [];
                private $startTime;
                
                public function __construct()
                {
                    $this->startTime = time();
                    $this->clients = Cache::get('video_clients', []);
                }
                
                /**
                 * Registrar heartbeat de cliente
                 */
                public function heartbeat($clientId, $data = [])
                {
                    $now = time();
                    
                    // Se é um cliente novo, contar
                    $isNew = !isset($this->clients[$clientId]);
                    
                    $this->clients[$clientId] = [
                        'id' => $clientId,
                        'platform' => $data['platform'] ?? 'unknown',
                        'version' => $data['app_version'] ?? '1.0.0',
                        'last_seen' => $now,
                        'first_seen' => $this->clients[$clientId]['first_seen'] ?? $now,
                        'views' => ($this->clients[$clientId]['views'] ?? 0) + 1,
                        'ip' => request()->ip()
                    ];
                    
                    // Salvar em cache (expira em 24h, mas renovamos a cada heartbeat)
                    Cache::put('video_clients', $this->clients, 1440); // 24 horas
                    
                    return [
                        'is_new' => $isNew,
                        'online_count' => $this->getOnlineCount(),
                        'client' => $this->clients[$clientId]
                    ];
                }
                
                /**
                 * Obter contagem de clientes online (últimos 5 minutos)
                 */
                public function getOnlineCount($minutes = 5)
                {
                    $now = time();
                    $cutoff = $now - ($minutes * 60);
                    
                    $count = 0;
                    foreach ($this->clients as $client) {
                        if ($client['last_seen'] >= $cutoff) {
                            $count++;
                        }
                    }
                    
                    return $count;
                }
                
                /**
                 * Listar clientes online
                 */
                public function getOnlineClients($minutes = 5)
                {
                    $now = time();
                    $cutoff = $now - ($minutes * 60);
                    
                    $onlineClients = [];
                    foreach ($this->clients as $client) {
                        if ($client['last_seen'] >= $cutoff) {
                            $onlineClients[] = [
                                'id' => $client['id'],
                                'platform' => $client['platform'],
                                'version' => $client['version'],
                                'last_seen' => date('Y-m-d H:i:s', $client['last_seen']),
                                'first_seen' => date('Y-m-d H:i:s', $client['first_seen']),
                                'views' => $client['views'],
                                'online_for' => $now - $client['last_seen'],
                                'ip' => $client['ip']
                            ];
                        }
                    }
                    
                    return $onlineClients;
                }
                
                /**
                 * Obter estatísticas
                 */
                public function getStats()
                {
                    $now = time();
                    $onlineCount = $this->getOnlineCount();
                    
                    // Contar por plataforma
                    $platforms = [];
                    $todayCount = 0;
                    $todayStart = strtotime('today');
                    
                    foreach ($this->clients as $client) {
                        $platform = $client['platform'];
                        $platforms[$platform] = ($platforms[$platform] ?? 0) + 1;
                        
                        // Clientes de hoje
                        if ($client['first_seen'] >= $todayStart) {
                            $todayCount++;
                        }
                    }
                    
                    return [
                        'online' => $onlineCount,
                        'total_today' => $todayCount,
                        'total_all' => count($this->clients),
                        'platforms' => $platforms,
                        'server_uptime' => $now - $this->startTime,
                        'updated_at' => date('Y-m-d H:i:s', $now)
                    ];
                }
                
                /**
                 * Limpar clientes inativos (mais de 1 hora)
                 */
                public function cleanup()
                {
                    $now = time();
                    $cutoff = $now - 3600; // 1 hora
                    
                    $before = count($this->clients);
                    
                    $this->clients = array_filter($this->clients, function($client) use ($cutoff) {
                        return $client['last_seen'] >= $cutoff;
                    });
                    
                    Cache::put('video_clients', $this->clients, 1440);
                    
                    return $before - count($this->clients);
                }
            };
        });
    }
    
    public function boot()
    {
        // Schedule cleanup diariamente
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            $schedule->call(function () {
                app('client.monitor')->cleanup();
            })->daily();
        });
    }
}