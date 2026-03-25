<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migra dados de 'settings' para 'system_settings' se ainda não tiverem sido migrados
     */
    public function up(): void
    {
        // Se a tabela settings existe e system_settings está vazia, migra os dados
        if (Schema::hasTable('settings') && Schema::hasTable('system_settings')) {
            try {
                $settingsRecord = DB::table('settings')->first();
                $systemSettingsCount = DB::table('system_settings')->count();

                if ($settingsRecord && $systemSettingsCount === 0) {
                    // Migra configurações para system_settings
                    DB::table('system_settings')->insert([
                        'api_endpoint' => $settingsRecord->api_endpoint ?? url('/api/videos'),
                        'api_key' => $settingsRecord->api_key ?? null,
                        'sync_interval' => $settingsRecord->sync_interval ?? 30,
                        'default_monitor' => $settingsRecord->default_monitor ?? 'principal',
                        'auto_close_delay' => $settingsRecord->auto_close_delay ?? 0,
                        'always_on_top' => $settingsRecord->always_on_top ?? false,
                        'start_with_windows' => $settingsRecord->start_with_windows ?? false,
                        'show_in_system_tray' => $settingsRecord->show_in_system_tray ?? true,
                        'enable_notifications' => $settingsRecord->enable_notifications ?? true,
                        'cache_location' => $settingsRecord->cache_location ?? null,
                        'max_cache_size' => $settingsRecord->max_cache_size ?? 10,
                        'auto_cleanup' => $settingsRecord->auto_cleanup ?? true,
                        'max_memory_usage' => $settingsRecord->max_memory_usage ?? 200,
                        'log_level' => $settingsRecord->log_level ?? 'info',
                        'enable_hardware_acceleration' => $settingsRecord->enable_hardware_acceleration ?? true,
                        'preload_videos' => $settingsRecord->preload_videos ?? false,
                        'enable_auto_update' => $settingsRecord->enable_auto_update ?? true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Erro ao migrar dados de settings para system_settings: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não faz nada na reversão para manter dados íntegros
    }
};
