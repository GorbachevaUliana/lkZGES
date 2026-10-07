<?php

namespace App\Enums;

enum ClientType: string
{
    case Individual   = 'individual';
    case Legal        = 'legal';
    case Entrepreneur = 'entrepreneur';

    public function label(): string
    {
        return match ($this) {
            self::Individual   => 'Физическое лицо',
            self::Legal        => 'Юридическое лицо',
            self::Entrepreneur => 'Индивидуальный предприниматель',
        };
    }

    /**
     * Короткое наименование — для бейджей в таблицах.
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::Individual   => 'Физлицо',
            self::Legal        => 'Юрлицо',
            self::Entrepreneur => 'ИП',
        };
    }

    /**
     * Цвет бейджа в админке.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Individual   => 'success',
            self::Legal        => 'warning',
            self::Entrepreneur => 'info',
        };
    }
    
    public function signatureMethod(): SignatureMethod
    {
        return match($this){
            self::Individual =>SignatureMethod::Pep,
            self::Legal, self::Entrepreneur   =>SignatureMethod::Ukep,
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
