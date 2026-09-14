<?php

namespace App\Enums;

enum SignatureMethod: string
{
    case Pep  = 'pep';   // простая ЭП — код подтверждения
    case Ukep = 'ukep';  // усиленная квалифицированная — файл .sig

    public function label(): string
    {
        return match ($this) {
            self::Pep  => 'Простая электронная подпись',
            self::Ukep => 'Усиленная квалифицированная подпись',
        };
    }
}