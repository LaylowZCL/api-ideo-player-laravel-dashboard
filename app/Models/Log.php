<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'time', 'event', 'status', 'video', 'level'
    ];
}