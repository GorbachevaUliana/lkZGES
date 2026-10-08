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

    /**
     * Blade-шаблон заявки по умолчанию. Используется, когда в базе
     * нет записи PdfTemplate для этого типа лица.
     */
    public function defaultApplicationView(): string
    {
        return match ($this) {
            self::Individual   => 'pdf.application_individual',
            self::Legal        => 'pdf.application_legal',
            self::Entrepreneur => 'pdf.application_entrepreneur',
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
