<?php

namespace App\Enums;

enum ClientType: string
{
    case Individual = 'individual';
    case Legal = 'legal';

    public function label(): string
    {
        return match($this) {
            self::Individual => 'Физическое лицо',
            self::Legal => 'Юридическое лицо',
        };
    }

    public static function labels(): array
    {
        return array_column(
            array_map(fn($t) => ['key' => $t->value, 'label' => $t->label()], self::cases()),
            'label',
            'key'
        );
    }
}
?>