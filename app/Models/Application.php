<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'template_id',
        'client_type',
        'data',
        'status',
        'generated_pdf_path',
        'contract_pdf_path',
        'admin_comment',
        'processed_at',
        'processed_by',
        'tariff_id',
        'property_id'
    ];

    protected $casts = [
        'data' => 'array',
        'processed_at' => 'datetime',
    ];

    protected $appends = [
        'applicant_name',
        'user_email',
        'account_number',
        'status_name',
        'generated_pdf_url',
    ];

    // ==================== CONSTANTS ====================

    const STATUS_NEW = 'new';
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const TYPE_INDIVIDUAL = 'individual';
    const TYPE_LEGAL = 'legal';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => 'Новая',
            self::STATUS_PENDING => 'Ожидает рассмотрения',
            self::STATUS_PROCESSING => 'В работе',
            self::STATUS_APPROVED => 'Одобрена',
            self::STATUS_REJECTED => 'Отклонена',
        ];
    }

    public static function getClientTypes(): array
    {
        return [
            self::TYPE_INDIVIDUAL => 'Физическое лицо',
            self::TYPE_LEGAL => 'Юридическое лицо',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ApplicationTemplate::class, 'template_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }

    // ==================== ACCESSORS ====================

    public function getStatusNameAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getClientTypeNameAttribute(): string
    {
        return self::getClientTypes()[$this->client_type] ?? $this->client_type;
    }

    public function getApplicantNameAttribute(): string
    {
        if ($this->client) {
            return $this->client->display_name;
        }

        $data = $this->data ?? [];
        return trim(($data['last_name'] ?? '').' '.($data['first_name'] ?? '').' '.($data['middle_name'] ?? ''));
    }

    public function getUserEmailAttribute(): string
    {
        return $this->user?->email ?? '';
    }

    public function getAccountNumberAttribute(): ?string
    {
        return $this->property?->account_number ?? null;
    }

    public function getGeneratedPdfUrlAttribute(): ?string
    {
        // PDF заявки сохраняется также как Document (TYPE_APPLICATION),
        // поэтому отдаём через защищённый роут documents.serve по document_id.
        $doc = $this->documents()->where('type', 'application_pdf')->first();
        return $doc ? route('documents.serve', $doc->id) : null;
    }

    public function getContractPdfUrlAttribute(): ?string
    {
        // Договор тоже сохраняется как Document (TYPE_CONTRACT).
        $doc = $this->documents()->where('type', 'contract')->first();
        return $doc ? route('documents.serve', $doc->id) : null;
    }

    public function getProcessorNameAttribute(): string
    {
        if (! $this->processor) {
            return '';
        }

        if (isset($this->processor->full_name)) {
            return $this->processor->full_name;
        }

        if (isset($this->processor->name)) {
            return $this->processor->name;
        }

        return trim(($this->processor->last_name ?? '').' '.
                    ($this->processor->first_name ?? '').' '.
                    ($this->processor->middle_name ?? ''));
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeIndividuals($query)
    {
        return $query->where('client_type', self::TYPE_INDIVIDUAL);
    }

    public function scopeLegal($query)
    {
        return $query->where('client_type', self::TYPE_LEGAL);
    }

    // ==================== METHODS ====================

    public function markAsProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    public function approve(int $processedBy, ?string $accountNumber = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'processed_at' => now(),
            'processed_by' => $processedBy,
        ]);

        if ($this->client && $accountNumber) {
            $this->client->activate($accountNumber);
        }
    }

    public function reject(int $processedBy, ?string $comment = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'processed_at' => now(),
            'processed_by' => $processedBy,
            'admin_comment' => $comment,
        ]);
    }

    public function attachContract(string $path): void
    {
        $this->update(['contract_pdf_path' => $path]);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function isProcessed(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_REJECTED]);
    }
}