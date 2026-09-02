<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'category_activity_logs';

    protected $fillable = [
        'category_id',
        'category_name',
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
