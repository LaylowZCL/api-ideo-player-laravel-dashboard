<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoSubtitle extends Model
{
    protected $fillable = [
        'video_id',
        'label',
        'language',
        'path',
        'url',
        'mime',
        'size',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
