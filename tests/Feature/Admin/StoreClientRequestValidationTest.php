<?php

namespace Tests\Feature\Admin;

use App\Http\Requests\Admin\StoreClientRequest;
use App\Models\Tariff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Аудит, Проблема №10.
 *
 * Лишние пробелы вокруг ':' в rules() ломали разбор параметров:
 * 'in            : individual,legal' давало параметры [' individual', 'legal']
 * (с пробелом), из-за чего 'individual' никогда не проходил in:; а
 * 'exists: tariffs,id' проверял таблицу, буквально названную ' tariffs',
 * которой не существует, поэтому exists: всегда проваливался.
 */
class StoreClientRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    private function baseProperty(int $tariffId): array
    {
        return [
            'account_number' => '100999',
            'tariff_id' => $tariffId,
            'locality' => 'Барнаул',
            'street' => 'Ленина',
            'house' => '1',
        ];
    }

    public function test_individual_client_type_passes_validation(): void
    {
        $tariff = Tariff::create(['name' => 'Тестовый тариф', 'price_1' => 5, 'price_2' => 6, 'price_3' => 7, 'starts_at' => now()->subYear()]);

        $validator = Validator::make([
            'client_type' => 'individual',
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'properties' => [$this->baseProperty($tariff->id)],
        ], (new StoreClientRequest())->rules());

        $this->assertTrue(
            $validator->passes(),
            'Ожидалось, что client_type=individual пройдёт валидацию. Ошибки: '
                . json_encode($validator->errors()->toArray(), JSON_UNESCAPED_UNICODE)
        );
    }

    public function test_legal_client_type_passes_validation(): void
    {
        $tariff = Tariff::create(['name' => 'Тестовый тариф', 'price_1' => 5, 'price_2' => 6, 'price_3' => 7, 'starts_at' => now()->subYear()]);

        $validator = Validator::make([
            'client_type' => 'legal',
            'company_name' => 'ООО Ромашка',
            'properties' => [$this->baseProperty($tariff->id)],
        ], (new StoreClientRequest())->rules());

        $this->assertTrue($validator->passes(), json_encode($validator->errors()->toArray(), JSON_UNESCAPED_UNICODE));
    }

    public function test_valid_existing_tariff_id_passes_exists_rule(): void
    {
        $tariff = Tariff::create(['name' => 'Тестовый тариф', 'price_1' => 5, 'price_2' => 6, 'price_3' => 7, 'starts_at' => now()->subYear()]);

        $validator = Validator::make([
            'client_type' => 'individual',
            'properties' => [$this->baseProperty($tariff->id)],
        ], (new StoreClientRequest())->rules());

        $this->assertFalse($validator->errors()->has('properties.0.tariff_id'));
    }

    public function test_nonexistent_tariff_id_still_fails_validation(): void
    {
        $validator = Validator::make([
            'client_type' => 'individual',
            'properties' => [$this->baseProperty(999999)],
        ], (new StoreClientRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('properties.0.tariff_id'));
    }

    public function test_invalid_client_type_still_fails_validation(): void
    {
        $tariff = Tariff::create(['name' => 'Тестовый тариф', 'price_1' => 5, 'price_2' => 6, 'price_3' => 7, 'starts_at' => now()->subYear()]);

        $validator = Validator::make([
            'client_type' => 'something_else',
            'properties' => [$this->baseProperty($tariff->id)],
        ], (new StoreClientRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('client_type'));
    }
}