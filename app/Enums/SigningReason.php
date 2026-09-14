<?php

namespace App\Enums;

enum SigningReason: string
{
    case PowerThreshold = 'power_threshold'; // мощность не ниже порога
    case ClientRequest  = 'client_request';  // потребитель попросил сам
    case NotRequired    = 'not_required';    // подпись не требуется

    public function label(): string
    {
        return match ($this) {
            self::PowerThreshold => 'Обязательно по мощности',
            self::ClientRequest  => 'По желанию потребителя',
            self::NotRequired    => 'Не требуется',
        };
    }
}