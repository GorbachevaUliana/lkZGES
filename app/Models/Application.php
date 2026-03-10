<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'user_id',
        'template_id',
        'data',
        'status'
    ];

    protected $casts = [
        'data' => 'array'
    ];
}
