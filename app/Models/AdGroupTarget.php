<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdGroupTarget extends Model
{
    protected $fillable = [
        'client_id',
        'machine_name',
        'user_name',
        'ad_group_id',
        'effective_at',
        'source',
    ];

    protected $casts = [
        'effective_at' => 'datetime',
    ];

    public function adGroup()
    {
        return $this->belongsTo(AdGroup::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
