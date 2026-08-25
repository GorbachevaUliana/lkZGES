<?php

namespace Tests\Feature\Client;

use App\Enums\ClientType;
use App\Enums\PropertyStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\MeterReading;
use App\Models\Property;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Аудит, Проблема №36.
 *
 * Client/DashboardController::getStats():
 *  1) totalDebt был захардкожен в 0 и никогда не пересчитывался.
 *  2) поиск последнего показания делал foreach по объектам клиента с
 *     отдельным запросом meterReadings() на каждой итерации (N+1).
 */
class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    private function makeClientWithProperty(): array
    {
        $user = User::factory()->create(['role' => UserRole::Client]);
        $client = Client::create([
            'user_id' => $user->id,
            'client_type' => ClientType::Individual->value,
            'last_name' => 'Тестов',
            'first_name' => 'Тест',
        ]);
        $tariff = Tariff::create(['name' => 'Тестовый тариф', 'price_1' => 5, 'price_2' => 6, 'price_3' => 7, 'starts_at' => now()->subYear()]);
        $property = Property::create([
            'client_id' => $client->id,
            'account_number' => '100' . random_int(100, 999999),
            'address' => 'Тестовый адрес',
            'status' => PropertyStatus::Active->value,
            'tariff_id' => $tariff->id,
        ]);

        return [$user, $client, $property, $tariff];
    }

    private function makeReading(Property $property, int $current, bool $isPaid, float $totalSum, $readingDate = null): MeterReading
    {
        return MeterReading::withoutEvents(function () use ($property, $current, $isPaid, $totalSum, $readingDate) {
            return MeterReading::create([
                'property_id' => $property->id,
                'current_value' => $current,
                'previous_value' => 0,
                'reading_date' => $readingDate ?? now(),
                'is_paid' => $isPaid,
                'total_sum' => $totalSum,
            ]);
        });
    }

    public function test_total_debt_is_calculated_from_unpaid_readings(): void
    {
        [$user, , $property] = $this->makeClientWithProperty();

        $this->makeReading($property, 100, isPaid: false, totalSum: 500.50, readingDate: now()->subMonths(3));
        $this->makeReading($property, 150, isPaid: false, totalSum: 250.25, readingDate: now()->subMonths(2));
        $this->makeReading($property, 200, isPaid: true, totalSum: 999.00, readingDate: now()->subMonth());

        $response = $this->actingAs($user)->get(route('client.dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->component('Client/Dashboard')
            ->where('stats.totalDebt', 750.75)
        );
    }

    public function test_total_debt_is_zero_when_everything_is_paid(): void
    {
        [$user, , $property] = $this->makeClientWithProperty();

        $this->makeReading($property, 100, isPaid: true, totalSum: 500.00);

        $response = $this->actingAs($user)->get(route('client.dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->component('Client/Dashboard')
            ->where('stats.totalDebt', 0)
        );
    }

    public function test_last_reading_is_the_most_recent_across_all_properties(): void
    {
        [$user, $client] = $this->makeClientWithProperty();
        $tariff = Tariff::first();

        $secondProperty = Property::create([
            'client_id' => $client->id,
            'account_number' => '200' . random_int(100, 999999),
            'address' => 'Второй адрес',
            'status' => PropertyStatus::Active->value,
            'tariff_id' => $tariff->id,
        ]);

        $property = Property::where('client_id', $client->id)->first();
        $this->makeReading($property, 100, isPaid: true, totalSum: 100, readingDate: now()->subDays(10));
        $this->makeReading($secondProperty, 777, isPaid: true, totalSum: 100, readingDate: now()->subDay());

        $response = $this->actingAs($user)->get(route('client.dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->component('Client/Dashboard')
            ->where('stats.lastReading', 777)
        );
    }

    public function test_stats_query_count_does_not_grow_with_number_of_properties(): void
    {
        [$user, $client] = $this->makeClientWithProperty();
        $tariff = Tariff::first();

        for ($i = 0; $i < 8; $i++) {
            Property::create([
                'client_id' => $client->id,
                'account_number' => '300' . $i . random_int(10, 99),
                'address' => "Объект $i",
                'status' => PropertyStatus::Active->value,
                'tariff_id' => $tariff->id,
            ]);
        }

        DB::enableQueryLog();
        $this->actingAs($user)->get(route('client.dashboard'));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(
            15,
            $queryCount,
            "Похоже, N+1 вернулся: на дашборд ушло $queryCount запросов при 9 объектах клиента."
        );
    }
}