<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_ad_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('ad_group_id')->constrained('ad_groups')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['client_id', 'ad_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_ad_group');
    }
};
