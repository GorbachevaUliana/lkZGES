<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case New = 'new';
    case Pending = 'pending';
    case Processing = 'processing';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::New => 'Новая',
            self::Pending => 'Ожидает рассмотрения',
            self::Processing => 'В работе',
            self::Approved => 'Одобрена',
            self::Rejected => 'Отклонена',
        };
    }

    public function isTerminal(): bool
    {
        return match($this) {
            self::Approved, self::Rejected => true,
            default => false,
        };
    }

    public static function labels(): array
    {
        return array_column(
            array_map(fn($s) => ['key' => $s->value, 'label' => $s->label()], self::cases()),
            'label',
            'key'
        );
    }
}