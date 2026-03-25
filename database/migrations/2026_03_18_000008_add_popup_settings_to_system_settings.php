<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('system_settings', 'popup_width')) {
                $table->unsignedInteger('popup_width')->default(960)->comment('Largura padrão do popup');
            }
            if (!Schema::hasColumn('system_settings', 'popup_height')) {
                $table->unsignedInteger('popup_height')->default(540)->comment('Altura padrão do popup');
            }
            if (!Schema::hasColumn('system_settings', 'popup_position')) {
                $table->enum('popup_position', ['center', 'top_left', 'top_right', 'bottom_left', 'bottom_right'])
                    ->default('center')
                    ->comment('Posição padrão do popup');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            if (Schema::hasColumn('system_settings', 'popup_position')) {
                $table->dropColumn('popup_position');
            }
            if (Schema::hasColumn('system_settings', 'popup_height')) {
                $table->dropColumn('popup_height');
            }
            if (Schema::hasColumn('system_settings', 'popup_width')) {
                $table->dropColumn('popup_width');
            }
        });
    }
};
