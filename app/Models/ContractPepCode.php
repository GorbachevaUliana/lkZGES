<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractPepCode extends Model
{
    protected $fillable = [
        'contract_id',
        'code_hash',
        'channel',
        'sent_to',
        'attempts',
        'expires_at',
        'confirmed_at',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    protected $hidden = [
        'code_hash',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    public function isUsable(): bool
    {
        return $this->confirmed_at === null && ! $this->isExpired();
    }
}   