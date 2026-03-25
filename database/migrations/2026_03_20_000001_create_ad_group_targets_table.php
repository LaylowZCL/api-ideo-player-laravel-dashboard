<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_group_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('machine_name');
            $table->string('user_name')->nullable();
            $table->foreignId('ad_group_id')->constrained('ad_groups')->cascadeOnDelete();
            $table->dateTime('effective_at')->nullable();
            $table->string('source')->default('json');
            $table->timestamps();

            $table->index(['machine_name', 'user_name']);
            $table->index(['ad_group_id']);
            $table->unique(['machine_name', 'user_name', 'ad_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_group_targets');
    }
};
