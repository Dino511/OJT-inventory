<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'location_activity_logs';

    protected $fillable = [
        'location_id',
        'location_name',
        'user_id',
        'user_name',
        'action',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];
}
