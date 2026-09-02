<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'company_activity_logs';

    protected $fillable = [
        'company_id',
        'company_name',
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
