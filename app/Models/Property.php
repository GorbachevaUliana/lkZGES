<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = ['client_id', 'account_number', 'address', 'status'];

    // Объект принадлежит клиенту
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // У объекта много показаний
    public function readings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    // У объекта могут быть заявки (например, на перепломбировку или ремонт)
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}