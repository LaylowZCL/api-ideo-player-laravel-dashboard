<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_ad_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignId('ad_group_id')->constrained('ad_groups')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['schedule_id', 'ad_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_ad_group');
    }
};
