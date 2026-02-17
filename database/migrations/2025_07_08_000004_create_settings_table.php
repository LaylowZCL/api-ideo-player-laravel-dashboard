<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('api_endpoint')->default(url('/api/videos'));
            $table->string('api_key')->nullable();
            $table->unsignedInteger('sync_interval')->default(30);
            $table->string('default_monitor');
            $table->boolean('always_on_top')->default(true);
            $table->integer('auto_close_delay')->default(0);
            $table->boolean('start_with_windows')->default(true);
            $table->boolean('show_in_system_tray')->default(true);
            $table->boolean('enable_notifications')->default(true);
            $table->string('cache_location');
            $table->integer('max_cache_size');
            $table->boolean('auto_cleanup')->default(true);
            $table->string('log_level');
            $table->integer('max_log_files')->default(10);
            $table->boolean('enable_auto_update')->default(true);
            $table->integer('max_memory_usage');
            $table->boolean('enable_hardware_acceleration')->default(true);
            $table->boolean('preload_videos')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
