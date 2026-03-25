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
        Schema::table('system_settings', function (Blueprint $table) {
            // Configurações de API (se não existirem)
            if (!Schema::hasColumn('system_settings', 'api_endpoint')) {
                $table->string('api_endpoint')->default(url('/api/videos'))->nullable();
            }
            if (!Schema::hasColumn('system_settings', 'api_key')) {
                $table->string('api_key')->nullable();
            }
            if (!Schema::hasColumn('system_settings', 'sync_interval')) {
                $table->unsignedInteger('sync_interval')->default(30)->comment('Em segundos');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumnIfExists(['api_endpoint', 'api_key', 'sync_interval']);
        });
    }
};
