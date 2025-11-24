<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('api_id')->nullable();
            $table->string('name');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('size');
            $table->string('duration');
            $table->boolean('cached')->default(false);
            $table->timestamp('last_sync')->nullable();
            $table->string('status');
            $table->boolean('is_active')->default(true);
            $table->string('url');
            $table->string('thumbnail_url')->nullable();
            $table->string('file_path')->nullable();

            $table->index('api_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};