<?php

namespace Tests\Unit;

use App\Enums\ClientType;
use App\Models\Client;
use Tests\TestCase;

class ClientDisplayNameTest extends TestCase
{
    /**
     * Модель не сохраняем — display_name это аксессор, база ему не нужна.
     */
    private function makeClient(string $clientType, array $attributes = []): Client
    {
        $client = new Client();
        $client->client_type = $clientType;

        foreach ($attributes as $key => $value) {
            $client->{$key} = $value;
        }

        return $client;
    }

    public function test_individual_is_shown_by_full_name(): void
    {
        $client = $this->makeClient(ClientType::Individual->value, [
            'last_name'   => 'Иванов',
            'first_name'  => 'Иван',
            'middle_name' => 'Иванович',
        ]);

        $this->assertSame('Иванов Иван Иванович', $client->display_name);
    }

    public function test_legal_is_shown_by_company_name(): void
    {
        $client = $this->makeClient(ClientType::Legal->value, [
            'company_name' => 'ООО «Ромашка»',
            'last_name'    => 'Иванов',
        ]);

        $this->assertSame('ООО «Ромашка»', $client->display_name);
    }

    public function test_entrepreneur_is_shown_by_full_name_with_prefix(): void
    {
        $client = $this->makeClient(ClientType::Entrepreneur->value, [
            'last_name'   => 'Иванов',
            'first_name'  => 'Иван',
            'middle_name' => 'Иванович',
        ]);

        $this->assertSame('ИП Иванов Иван Иванович', $client->display_name);
    }

    /**
     * У ИП нет наименования организации. Даже если company_name почему-то
     * заполнен, показывать нужно ФИО — иначе в договоре окажется не та сторона.
     */
    public function test_entrepreneur_ignores_company_name(): void
    {
        $client = $this->makeClient(ClientType::Entrepreneur->value, [
            'company_name' => 'ООО «Ромашка»',
            'last_name'    => 'Иванов',
            'first_name'   => 'Иван',
        ]);

        $this->assertSame('ИП Иванов Иван', $client->display_name);
    }

    public function test_entrepreneur_without_name_falls_back_to_placeholder(): void
    {
        $client = $this->makeClient(ClientType::Entrepreneur->value);

        $this->assertSame('ФИО не указано', $client->display_name);
    }
}