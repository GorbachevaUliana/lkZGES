<?php

namespace App\Models;

use App\Enums\ContractStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_id',
        'client_id',
        'client_type',
        'file_path',
        'original_name',
        'file_hash',
        'status',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // ==================== METHODS ====================

    /**
     * Посчитать SHA-256 текущего файла на диске.
     * Отдельным методом, потому что понадобится в двух местах:
     * при загрузке (записать) и при подписании (сверить).
     */
    public function calculateFileHash(): string
    {
        return hash('sha256', Storage::disk('local')->get($this->file_path));
    }

    /**
     * Защита от подмены PDF между загрузкой и подписанием.
     */
    public function fileIsIntact(): bool
    {
        return hash_equals($this->file_hash, $this->calculateFileHash());
    }

    public function isAwaitingClient(): bool
    {
        return $this->status === ContractStatus::AwaitingClient->value;
    }

    public function isSigned(): bool
    {
        return in_array($this->status, [
            ContractStatus::Signed->value,
            ContractStatus::Active->value,
        ], true);
    }

    public function statusLabel(): string
    {
        return ContractStatus::labels()[$this->status] ?? $this->status;
    }
}