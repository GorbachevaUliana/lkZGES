<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractSignature extends Model
{
    protected $fillable = [
        'contract_id',
        'signer',
        'method',
        'signed_at',
        'document_hash',
        'signed_by_user_id',
        'signature_file_path',
        'certificate_subject',
        'certificate_serial',
        'certificate_inn',
        'certificate_valid_from',
        'certificate_valid_to',
        'pep_channel',
        'pep_sent_to',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'signed_at'              => 'datetime',
        'certificate_valid_from' => 'datetime',
        'certificate_valid_to'   => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function signedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_user_id');
    }
}