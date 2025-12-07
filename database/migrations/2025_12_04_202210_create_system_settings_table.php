<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            
            // Configurações de Exibição
            $table->enum('default_monitor', ['principal', 'secundario', 'todos'])
                  ->default('principal');
            $table->unsignedInteger('auto_close_delay')
                  ->default(0)
                  ->comment('Segundos, 0 = manual');
            $table->boolean('always_on_top')->default(false);
            
            // Configurações do Sistema
            $table->boolean('start_with_windows')->default(false);
            $table->boolean('show_in_system_tray')->default(true);
            $table->boolean('enable_notifications')->default(true);
            
            // Armazenamento e Cache
            $table->string('cache_location')->nullable();
            $table->unsignedInteger('max_cache_size')
                  ->default(10)
                  ->comment('Em GB');
            $table->boolean('auto_cleanup')->default(true);
            
            // Performance
            $table->unsignedInteger('max_memory_usage')
                  ->default(200)
                  ->comment('Em MB');
            $table->enum('log_level', ['error', 'warning', 'info', 'debug'])
                  ->default('info');
            $table->boolean('enable_hardware_acceleration')->default(true);
            $table->boolean('preload_videos')->default(false);
            $table->boolean('enable_auto_update')->default(true);
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
