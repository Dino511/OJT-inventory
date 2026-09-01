<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseUnit extends Model
{
    use HasFactory;

    protected $table = 'base_units'; // Update this if your table name is different
    protected $guarded = ['id'];
}