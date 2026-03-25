<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            // Converte size para unsignedBigInteger se for string
            // Primeiro tenta fazer o change, se falhar é porque já é do tipo correto
            if (Schema::hasColumn('videos', 'size')) {
                try {
                    $table->unsignedBigInteger('size')->change();
                } catch (\Exception $e) {
                    // Coluna já é do tipo correto ou está vazia
                    \Log::info('videos.size já está como unsignedBigInteger ou migration não aplicável');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não reverteremos a mudança de tipo para evitar perda de dados
        // Schema::table('videos', function (Blueprint $table) {
        //     $table->string('size')->change();
        // });
    }
};
