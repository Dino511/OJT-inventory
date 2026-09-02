<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'inventory_activity_logs';

    protected $fillable = [
        'inventory_id',
        'product_name',
        'product_code',
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
