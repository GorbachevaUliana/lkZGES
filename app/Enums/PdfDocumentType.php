<?php

namespace App\Enums;

enum PdfDocumentType: string
{
    case Application = 'application';
    case Contract = 'contract';
    case Other = 'other';

    public function label(): string
    {
        return match($this) {
            self::Application => 'Заявка',
            self::Contract => 'Договор',
            self::Other => 'Другое',
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