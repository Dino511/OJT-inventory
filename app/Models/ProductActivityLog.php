<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'product_activity_logs';

    protected $fillable = [
        'product_id',
        'product_name',
        'product_code',
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
