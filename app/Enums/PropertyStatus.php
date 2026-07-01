<?php

namespace App\Enums;

enum PropertyStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';

    public function label(): string
    {
        return match($this) {
            self::Active => 'Активен',
            self::Inactive => 'Неактивен',
            self::Pending => 'Ожидает активации',
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