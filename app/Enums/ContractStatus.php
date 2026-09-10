<?php

namespace App\Enums;

enum ContractStatus: string
{
    case Draft          = 'draft';           // загружен, ещё не отправлен клиенту
    case AwaitingClient = 'awaiting_client'; // ждём подпись клиента
    case Signed         = 'signed';          // подписан с двух сторон
    case Active         = 'active';          // действует
    case Terminated     = 'terminated';      // расторгнут

    public function label(): string
    {
        return match ($this) {
            self::Draft          => 'Черновик',
            self::AwaitingClient => 'Ожидает подписи клиента',
            self::Signed         => 'Подписан',
            self::Active         => 'Действует',
            self::Terminated     => 'Расторгнут',
        };
    }

    public static function labels(): array
    {
        return array_column(
            array_map(fn ($s) => ['key' => $s->value, 'label' => $s->label()], self::cases()),
            'label',
            'key'
        );
    }
}