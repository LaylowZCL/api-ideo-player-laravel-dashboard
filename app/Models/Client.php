<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'client_id',
        'client_token',
        'hostname',
        'platform',
        'version',
        'ip_address',
        'api_key',
        'ad_dn',
        'ad_sid',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function adGroups()
    {
        return $this->belongsToMany(AdGroup::class, 'client_ad_group');
    }

    public function schedules()
    {
        return $this->belongsToMany(Schedule::class, 'schedule_client');
    }

    public function adGroupTargets()
    {
        return $this->hasMany(AdGroupTarget::class);
    }
}
