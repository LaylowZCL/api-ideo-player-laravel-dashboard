<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdGroup extends Model
{
    protected $fillable = [
        'name',
        'dn',
        'sid',
        'email',
        'source',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_ad_group');
    }

    public function schedules()
    {
        return $this->belongsToMany(Schedule::class, 'schedule_ad_group');
    }
}
