<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'title',
        'video_url',
        'video_id',
        'time',
        'days',
        'monitor',
        'active',
        'duration',
        'subtitle_url',
        'window_config',
        'campaign_id',
        'priority',
    ];

    protected $casts = [
        'days' => 'array',
        'active' => 'boolean',
        'window_config' => 'array',
        'priority' => 'integer',
    ];

    /**
     * Get window configuration with defaults
     */
    public function getWindowConfigAttribute($value)
    {
        $default = [
            'position' => [
                'anchor' => 'bottom-right',
                'x' => null,
                'y' => null,
                'margin' => 50,
            ],
            'size' => [
                'width' => 854,
                'height' => 480,
            ],
            'flags' => [
                'always_on_top' => true,
                'fullscreen' => false,
                'frame' => true,
            ],
        ];

        if (!$value) {
            return $default;
        }

        return array_merge($default, (array) $value);
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function targetGroups()
    {
        return $this->belongsToMany(AdGroup::class, 'schedule_ad_group');
    }

    public function targetClients()
    {
        return $this->belongsToMany(Client::class, 'schedule_client');
    }
}
