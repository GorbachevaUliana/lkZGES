<?php

namespace Tests\Feature;

use App\Enums\ClientType;
use App\Enums\PropertyStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Найдено 24.08: меню в ClientLayout ломалось на странице "Мои документы"
 * (показывались только "Главная" и "Мои документы"), потому что
 * hasActiveProperties передавал только Client/DashboardController::index(),
 * а не documents(). Отдельно — user.permissions вообще нигде не
 * расшаривались, из-за чего меню сотрудника без роли admin в AdminLayout
 * всегда было бы пустым. Оба теперь идут из HandleInertiaRequests::share().
 */
class SharedInertiaPropsTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_with_active_property_sees_full_menu_on_documents_page(): void
    {
        $user = User::factory()->create(['role' => UserRole::Client]);
        $client = Client::create([
            'user_id' => $user->id,
            'client_type' => ClientType::Individual->value,
            'last_name' => 'Активов',
        ]);
        Property::create([
            'client_id' => $client->id,
            'account_number' => '100777',
            'address' => 'Активный адрес',
            'status' => PropertyStatus::Active->value,
        ]);

        // Именно страница "Мои документы" раньше не передавала
        // hasActiveProperties/properties сама по себе.
        $response = $this->actingAs($user)->get(route('client.documents'));

        $response->assertInertia(fn ($page) => $page
            ->where('hasActiveProperties', true)
        );
    }

    public function test_client_without_active_property_does_not_see_full_menu(): void
    {
        $user = User::factory()->create(['role' => UserRole::Client]);
        Client::create([
            'user_id' => $user->id,
            'client_type' => ClientType::Individual->value,
            'last_name' => 'Безобъектов',
        ]);

        $response = $this->actingAs($user)->get(route('client.documents'));

        $response->assertInertia(fn ($page) => $page
            ->where('hasActiveProperties', false)
        );
    }

    public function test_staff_permissions_are_shared_globally(): void
    {
        $staff = User::factory()->create([
            'role' => UserRole::Staff,
            'permissions' => ['dashboard', 'clients', 'tickets'],
        ]);

        $response = $this->actingAs($staff)->get(route('admin.dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('auth.user.permissions', ['clients', 'tickets'])
        );
    }

    public function test_admin_page_load_does_not_query_client_properties(): void
    {
        // hasActiveProperties считается только для role=client — у
        // админа/сотрудника не должно быть лишнего запроса на каждый
        // переход по страницам.
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->missing('hasActiveProperties')
        );
    }
}