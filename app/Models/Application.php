<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\ClientType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use SoftDeletes;
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
        // Раньше отсутствовали здесь — у всех троих есть рабочий
        // get...Attribute() ниже по файлу, но без записи в $appends
        // Eloquent не включает их в JSON вообще. contract_pdf_url —
        // ровно то, из-за чего в карточке всегда было «Договор ещё не
        // загружен», даже когда договор был реально загружен.
        'contract_pdf_url',
        'client_type_name',
        'processor_name',
    ];

    // ==================== CONSTANTS ====================

    public static function getStatuses(): array
    {
        return ApplicationStatus::labels();
    }

    public static function getClientTypes(): array
    {
        return ClientType::labels();
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

        // Юрлицо: фамилии/имени у него нет, имя заявителя — это название
        // организации. Раньше эта ветка (когда client ещё не создан)
        // собирала строку только из ФИО, поэтому у заявок юрлиц выходило
        // пусто / «Не указано Не указано» (Ю-6).
        if (($this->client_type ?? null) === ClientType::Legal->value) {
            return $data['company_name'] ?? 'Название не указано';
        }

        $name = trim(($data['last_name'] ?? '').' '.($data['first_name'] ?? '').' '.($data['middle_name'] ?? ''));

        return $name !== '' ? $name : 'Не указано';
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
        $docs = $this->relationLoaded('documents')
            ? $this->documents
            : $this->documents()->get();

        // Ищем по значению enum (PdfDocumentType::Application = 'application'),
        // с которым документ и сохраняется в ApplicationSubmitService. Раньше
        // здесь был хардкод 'application_pdf' — тип, которого не существует,
        // поэтому сгенерированная заявка не находилась и показывалось
        // «Файл не найден», хотя документ был (Ю-7).
        $doc = $docs->firstWhere('type', \App\Enums\PdfDocumentType::Application->value);
        return $doc ? route('documents.serve', $doc->id) : null;
    }

    public function getContractPdfUrlAttribute(): ?string
    {
        $docs = $this->relationLoaded('documents')
            ? $this->documents
            : $this->documents()->get();

        $doc = $docs->firstWhere('type', 'contract');
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
        return $query->where('status', ApplicationStatus::Pending->value);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', ApplicationStatus::Processing->value);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', ApplicationStatus::Approved->value);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', ApplicationStatus::Rejected->value);
    }

    public function scopeIndividuals($query)
    {
        return $query->where('client_type', ClientType::Individual->value);
    }

    public function scopeLegal($query)
    {
        return $query->where('client_type', ClientType::Legal->value);
    }

    // ==================== METHODS ====================

    public function markAsProcessing(): void
    {
        $this->update(['status' => ApplicationStatus::Processing->value]);
    }

    public function approve(int $processedBy, ?string $accountNumber = null): void
    {
        $this->update([
            'status' => ApplicationStatus::Approved->value,
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
            'status' => ApplicationStatus::Rejected->value,
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
        return in_array($this->status, [
            ApplicationStatus::Pending->value,
            ApplicationStatus::Processing->value,]);
    }

    public function isProcessed(): bool
    {
        return in_array($this->status, [
            ApplicationStatus::Approved->value,
            ApplicationStatus::Rejected->value,]);
    }
}