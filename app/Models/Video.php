<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'api_id', 'name', 'title', 'description', 'size', 'duration', 'cached', 
        'last_sync', 'status', 'url', 'file_path', 'thumbnail_url', 'is_active'
    ];

    protected $casts = [
        'cached' => 'boolean',
        'last_sync' => 'datetime',
        'is_active' => 'boolean',
        'size' => 'integer'
    ];

    // Relacionamento com agendamentos
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}