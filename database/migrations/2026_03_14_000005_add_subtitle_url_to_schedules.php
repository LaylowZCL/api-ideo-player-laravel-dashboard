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
        Schema::table('schedules', function (Blueprint $table) {
            // Adiciona URL de legendas se não existir
            if (!Schema::hasColumn('schedules', 'subtitle_url')) {
                $table->string('subtitle_url')->nullable()->comment('URL do arquivo de legendas SRT');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumnIfExists('subtitle_url');
        });
    }
};
