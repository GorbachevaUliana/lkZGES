<?php

namespace App\Enums;

enum SignerType: string
{
    case Organization = 'organization';
    case Client       = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Organization => 'Организация',
            self::Client       => 'Потребитель',
        };
    }
}