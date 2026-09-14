<?php

namespace App\Enums;

enum ClientType: string
{
    case Individual = 'individual';
    case Legal      = 'legal';

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Физическое лицо',
            self::Legal      => 'Юридическое лицо',
        };
    }

/**
 * Каким способом клиент этого типа подписывает договор.
 *
 * ВНИМАНИЕ: здесь намеренно нет default.
 * Когда появится ИП, PHP бросит UnhandledMatchError, и это правильно —
 * лучше падение, чем молча выданная физлицу подпись вместо УКЭП.
 * Не добавлять default. Добавлять кейсы.
 */

    public function signatureMethod(): SignatureMethod
    {
        return match($this){
            self::Individual => signatureMethod::Pep,
            self::Legal      => signatureMethod ::Ukep,
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
