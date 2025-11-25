<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'title', 'video_url', 'time', 'days', 'monitor', 'active', 'duration'
    ];

    protected $casts = [
        'days' => 'array',
        'active' => 'boolean',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}