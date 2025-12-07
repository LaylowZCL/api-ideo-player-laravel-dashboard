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
        Schema::create('video_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('video_id')->nullable()->constrained('videos')->onDelete('set null');
            $table->string('video_title')->nullable();
            $table->string('event_type'); // popup_opened, playback_started, completed, etc.
            $table->json('event_data')->nullable(); // Dados específicos do evento
            $table->decimal('playback_position', 10, 2)->nullable()->default(0); // Posição em segundos
            $table->decimal('playback_duration', 10, 2)->nullable()->default(0); // Duração total em segundos
            $table->string('user_agent')->nullable();
            $table->string('platform')->nullable(); // windows, mac, linux
            $table->string('app_version')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('session_id')->nullable();
            $table->string('trigger_type')->nullable()->default('scheduled'); // scheduled, manual
            $table->boolean('completed')->default(false);
            $table->timestamp('viewed_at')->useCurrent();
            // Índices para performance
            $table->index('video_id');
            $table->index('event_type');
            $table->index('viewed_at');
            $table->index('completed');
            $table->index(['video_id', 'viewed_at']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_reports');
    }
};
