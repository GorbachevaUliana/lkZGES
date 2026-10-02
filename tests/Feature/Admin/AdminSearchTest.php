<?php

namespace Tests\Feature\Admin;

use App\Enums\PropertyStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Property;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Поиск в админских списках работает по всей базе, а не по
 * загруженной странице. Проверяем на записи, которая заведомо
 * не попадает в первую страницу выдачи.
 */
class AdminSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    }

    private function makeClient(string $lastName, string $accountNumber): Client
    {
        $user = User::factory()->create(['role' => UserRole::Client]);

        $client = Client::create([
            'user_id'     => $user->id,
            'client_type' => 'individual',
            'last_name'   => $lastName,
            'first_name'  => 'Иван',
            'phone'       => '89000000000',
        ]);

        Property::create([
            'client_id'      => $client->id,
            'tariff_id'      => Tariff::first()?->id,
            'account_number' => $accountNumber,
            'address'        => 'ул. Тестовая, д. 1',
            'status'         => PropertyStatus::Active->value,
        ]);

        return $client;
    }

    public function test_client_search_finds_record_beyond_first_page(): void
    {
        // Много однотипных записей, чтобы искомая не попала в первую страницу
        for ($i = 0; $i < 60; $i++) {
            $this->makeClient('Петров', "1000{$i}");
        }

        $this->makeClient('Нужный', '999999');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.clients.index', ['search' => 'Нужный']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('clients.meta.total', 1)
            ->where('clients.data.0.last_name', 'Нужный'));
    }

    public function test_search_uses_case_insensitive_operator_on_postgres(): void
    {
        $this->makeClient('Сидоров', '555555');

        // На PostgreSQL поиск должен идти через ILIKE — это единственное,
        // что делает кириллицу регистронезависимой. В тестах база SQLite,
        // где ILIKE нет, поэтому проверяем сам выбор оператора,
        // а не результат поиска.
        DB::enableQueryLog();

        $this->actingAs($this->admin)
            ->get(route('admin.clients.index', ['search' => 'сидоров']));

        $queries = collect(DB::getQueryLog())->pluck('query')->implode(' ');

        DB::disableQueryLog();

        $expected = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $this->assertStringContainsStringIgnoringCase($expected, $queries);
    }

    public function test_client_search_finds_by_account_number(): void
    {
        $this->makeClient('Иванов', '777777');
        $this->makeClient('Петров', '888888');

        $this->actingAs($this->admin)
            ->get(route('admin.clients.index', ['search' => '777777']))
            ->assertInertia(fn ($page) => $page
                ->where('clients.meta.total', 1)
                ->where('clients.data.0.last_name', 'Иванов'));
    }

    public function test_empty_search_returns_everything(): void
    {
        $this->makeClient('Иванов', '111111');
        $this->makeClient('Петров', '222222');

        $this->actingAs($this->admin)
            ->get(route('admin.clients.index'))
            ->assertInertia(fn ($page) => $page->where('clients.meta.total', 2));
    }
}