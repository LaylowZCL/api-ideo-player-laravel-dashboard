<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_group_targets', function (Blueprint $table) {
            if (!Schema::hasColumn('ad_group_targets', 'user_display_name')) {
                $table->string('user_display_name')->nullable()->after('user_name');
            }

            if (!Schema::hasColumn('ad_group_targets', 'user_email')) {
                $table->string('user_email')->nullable()->after('user_display_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ad_group_targets', function (Blueprint $table) {
            if (Schema::hasColumn('ad_group_targets', 'user_email')) {
                $table->dropColumn('user_email');
            }

            if (Schema::hasColumn('ad_group_targets', 'user_display_name')) {
                $table->dropColumn('user_display_name');
            }
        });
    }
};
