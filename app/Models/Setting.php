<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated Use SystemSetting model instead for new functionality.
 * This model is kept for backward compatibility.
 */
class Setting extends Model
{
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
        'max_log_files',
        'enable_hardware_acceleration',
        'preload_videos',
        'enable_auto_update',
    ];

    protected $casts = [
        'cached' => 'boolean',
        'last_sync' => 'datetime',
        'always_on_top' => 'boolean',
        'start_with_windows' => 'boolean',
        'show_in_system_tray' => 'boolean',
        'enable_notifications' => 'boolean',
        'auto_cleanup' => 'boolean',
        'enable_hardware_acceleration' => 'boolean',
        'preload_videos' => 'boolean',
        'enable_auto_update' => 'boolean',
    ];
}
