<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('system_settings') || !Schema::hasColumn('system_settings', 'popup_position')) {
            return;
        }

        DB::table('system_settings')
            ->whereNull('popup_position')
            ->update(['popup_position' => 'bottom_right']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE system_settings
                MODIFY popup_position ENUM('center', 'top_left', 'top_right', 'bottom_left', 'bottom_right')
                NOT NULL DEFAULT 'bottom_right'
            ");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('system_settings') || !Schema::hasColumn('system_settings', 'popup_position')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE system_settings
                MODIFY popup_position ENUM('center', 'top_left', 'top_right', 'bottom_left', 'bottom_right')
                NOT NULL DEFAULT 'center'
            ");
        }
    }
};
