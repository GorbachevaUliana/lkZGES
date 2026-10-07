<?php

namespace Tests\Unit;

use App\Enums\ClientType;
use App\Enums\SignatureMethod;
use Tests\TestCase;

class ClientTypeTest extends TestCase
{
    public function test_individual_signs_with_pep(): void
    {
        $this->assertSame(SignatureMethod::Pep, ClientType::Individual->signatureMethod());
    }

    public function test_legal_signs_with_ukep(): void
    {
        $this->assertSame(SignatureMethod::Ukep, ClientType::Legal->signatureMethod());
    }

    public function test_entrepreneur_signs_with_ukep(): void
    {
        $this->assertSame(SignatureMethod::Ukep, ClientType::Entrepreneur->signatureMethod());
    }

    /**
     * Страховка от забытого кейса.
     *
     * Если в энум добавят новый тип лица, а в signatureMethod() его не опишут,
     * match бросит UnhandledMatchError и этот тест упадёт — вместо того,
     * чтобы кто-то молча получил не ту подпись.
     */
    public function test_every_client_type_has_a_signature_method(): void
    {
        foreach (ClientType::cases() as $type) {
            $this->assertInstanceOf(
                SignatureMethod::class,
                $type->signatureMethod(),
                "У типа лица {$type->value} не задан способ подписи"
            );
        }
    }

    public function test_every_client_type_has_label_short_label_and_color(): void
    {
        foreach (ClientType::cases() as $type) {
            $this->assertNotSame('', $type->label(),      "У типа лица {$type->value} пустое наименование");
            $this->assertNotSame('', $type->shortLabel(), "У типа лица {$type->value} пустое короткое наименование");
            $this->assertNotSame('', $type->badgeColor(), "У типа лица {$type->value} не задан цвет бейджа");
        }
    }

    public function test_labels_map_contains_every_case(): void
    {
        $labels = ClientType::labels();

        $this->assertCount(count(ClientType::cases()), $labels);
        $this->assertSame('Индивидуальный предприниматель', $labels['entrepreneur']);
    }
}