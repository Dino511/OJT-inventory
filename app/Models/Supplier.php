<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'secondary_contact_person',
        'phone',
        'secondary_phone',
        'email',
        'address',
    ];
}
