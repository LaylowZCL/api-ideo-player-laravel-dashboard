<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'name', 'title', 'size', 'duration', 'cached', 'last_sync', 'status', 'url'
    ];

    protected $casts = [
        'cached' => 'boolean',
        'last_sync' => 'datetime',
    ];
}