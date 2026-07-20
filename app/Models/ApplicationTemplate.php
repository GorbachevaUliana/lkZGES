<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationTemplate extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'client_type',
        'content',
        'is_active',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
    ];
}
