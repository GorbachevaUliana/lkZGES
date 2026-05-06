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

    // ==================== CONSTANTS ====================

    const STATUS_PENDING = 'pending';

    const STATUS_PROCESSING = 'processing';

    const STATUS_APPROVED = 'approved';

    const STATUS_REJECTED = 'rejected';

    const TYPE_INDIVIDUAL = 'individual';

    const TYPE_LEGAL = 'legal';

    public static function getStatuses(): array
    {
        return [
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

    /**
     * Пользователь, подавший заявку
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Клиент (создаётся при подаче заявки)
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Шаблон заявки
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ApplicationTemplate::class, 'template_id');
    }

    /**
     * Сотрудник, обработавший заявку
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Документы, связанные с заявкой
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    // ==================== ACCESSORS ====================

    /**
     * Название статуса на русском
     */
    public function getStatusNameAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Название типа клиента на русском
     */
    public function getClientTypeNameAttribute(): string
    {
        return self::getClientTypes()[$this->client_type] ?? $this->client_type;
    }

    /**
     * ФИО заявителя
     */
    public function getApplicantNameAttribute(): string
    {
        if ($this->client) {
            return $this->client->display_name;
        }

        // Если клиент ещё не создан, берём из data
        $data = $this->data ?? [];

        return trim(($data['last_name'] ?? '').' '.($data['first_name'] ?? '').' '.($data['middle_name'] ?? ''));
    }

    /**
     * URL для скачивания сгенерированного PDF
     */
    public function getGeneratedPdfUrlAttribute(): ?string
    {
        return $this->generated_pdf_path
            ? asset('storage/'.$this->generated_pdf_path)
            : null;
    }

    /**
     * URL для скачивания договора
     */
    public function getContractPdfUrlAttribute(): ?string
    {
        return $this->contract_pdf_path
            ? asset('storage/'.$this->contract_pdf_path)
            : null;
    }

    /**
     * Имя обработавшего сотрудника
     * Использует full_name или name из модели User
     */
    public function getProcessorNameAttribute(): string
    {
        if (! $this->processor) {
            return '';
        }

        // Проверяем, есть ли accessor getFullNameAttribute или full_name
        if (isset($this->processor->full_name)) {
            return $this->processor->full_name;
        }

        // Проверяем, есть ли accessor getNameAttribute или name
        if (isset($this->processor->name)) {
            return $this->processor->name;
        }

        // Формируем из отдельных полей
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

    /**
     * Перевести в статус "В работе"
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    /**
     * Одобрить заявку
     */
    public function approve(int $processedBy, ?string $accountNumber = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'processed_at' => now(),
            'processed_by' => $processedBy,
        ]);

        // Активируем клиента
        if ($this->client && $accountNumber) {
            $this->client->activate($accountNumber);
        }
    }

    /**
     * Отклонить заявку
     */
    public function reject(int $processedBy, ?string $comment = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'processed_at' => now(),
            'processed_by' => $processedBy,
            'admin_comment' => $comment,
        ]);
    }

    /**
     * Загрузить договор (путь к файлу)
     */
    public function attachContract(string $path): void
    {
        $this->update(['contract_pdf_path' => $path]);
    }

    /**
     * Можно ли редактировать заявку
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Завершена ли обработка заявки
     */
    public function isProcessed(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_REJECTED]);
    }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }
}
