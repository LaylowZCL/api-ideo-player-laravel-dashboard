<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        // Configurações de API
        'api_endpoint',
        'api_key',
        'sync_interval',
        
        // Configurações de Exibição
        'default_monitor',
        'auto_close_delay',
        'always_on_top',
        
        // Configurações do Sistema
        'start_with_windows',
        'show_in_system_tray',
        'enable_notifications',
        
        // Armazenamento e Cache
        'cache_location',
        'max_cache_size',
        'auto_cleanup',
        
        // Performance
        'max_memory_usage',
        'log_level',
        'enable_hardware_acceleration',
        'preload_videos',
        'enable_auto_update',
    ];

    protected $casts = [
        'always_on_top' => 'boolean',
        'start_with_windows' => 'boolean',
        'show_in_system_tray' => 'boolean',
        'enable_notifications' => 'boolean',
        'auto_cleanup' => 'boolean',
        'enable_hardware_acceleration' => 'boolean',
        'preload_videos' => 'boolean',
        'enable_auto_update' => 'boolean',
    ];

    protected $attributes = [
        // Valores padrão baseados no seu código
        'api_endpoint' => null,
        'sync_interval' => 30,
        'default_monitor' => 'principal',
        'auto_close_delay' => 0,
        'always_on_top' => true,
        'start_with_windows' => true,
        'show_in_system_tray' => true,
        'enable_notifications' => true,
        'cache_location' => 'C:\\VideoScheduler\\Cache',
        'max_cache_size' => 5,
        'auto_cleanup' => true,
        'max_memory_usage' => 200,
        'log_level' => 'info',
        'enable_hardware_acceleration' => true,
        'preload_videos' => true,
        'enable_auto_update' => true,
    ];

    public static function getCurrentSettings()
    {
        $settings = self::orderBy('created_at', 'desc')->first();
        
        if (!$settings) {
            $settings = self::create([
                'api_endpoint' => config('services.video_api.endpoint'),
            ]);
        }

        if (!$settings->api_endpoint) {
            $settings->api_endpoint = config('services.video_api.endpoint');
            $settings->save();
        }
        
        return $settings;
    }

    public function toVueFormat()
    {
        return [
            // Configurações de API
            'apiEndpoint' => $this->api_endpoint,
            'apiKey' => $this->api_key,
            'syncInterval' => $this->sync_interval,
            
            // Configurações de Exibição
            'defaultMonitor' => $this->default_monitor,
            'autoCloseDelay' => $this->auto_close_delay,
            'alwaysOnTop' => $this->always_on_top,
            
            // Configurações do Sistema
            'startWithWindows' => $this->start_with_windows,
            'showInSystemTray' => $this->show_in_system_tray,
            'enableNotifications' => $this->enable_notifications,
            
            // Armazenamento e Cache
            'cacheLocation' => $this->cache_location,
            'maxCacheSize' => $this->max_cache_size,
            'autoCleanup' => $this->auto_cleanup,
            
            // Performance
            'maxMemoryUsage' => $this->max_memory_usage,
            'logLevel' => $this->log_level,
            'enableHardwareAcceleration' => $this->enable_hardware_acceleration,
            'preloadVideos' => $this->preload_videos,
            'enableAutoUpdate' => $this->enable_auto_update,
        ];
    }

    public static function fromVueFormat(array $vueData)
    {
        return [
            'api_endpoint' => $vueData['apiEndpoint'] ?? config('services.video_api.endpoint'),
            'api_key' => $vueData['apiKey'] ?? '',
            'sync_interval' => $vueData['syncInterval'] ?? 30,
            
            'default_monitor' => $vueData['defaultMonitor'] ?? 'principal',
            'auto_close_delay' => $vueData['autoCloseDelay'] ?? 0,
            'always_on_top' => $vueData['alwaysOnTop'] ?? true,
            
            'start_with_windows' => $vueData['startWithWindows'] ?? true,
            'show_in_system_tray' => $vueData['showInSystemTray'] ?? true,
            'enable_notifications' => $vueData['enableNotifications'] ?? true,
            
            'cache_location' => $vueData['cacheLocation'] ?? 'C:\\VideoScheduler\\Cache',
            'max_cache_size' => $vueData['maxCacheSize'] ?? 5,
            'auto_cleanup' => $vueData['autoCleanup'] ?? true,
            
            'max_memory_usage' => $vueData['maxMemoryUsage'] ?? 200,
            'log_level' => $vueData['logLevel'] ?? 'info',
            'enable_hardware_acceleration' => $vueData['enableHardwareAcceleration'] ?? true,
            'preload_videos' => $vueData['preloadVideos'] ?? true,
            'enable_auto_update' => $vueData['enableAutoUpdate'] ?? true,
        ];
    }
}
