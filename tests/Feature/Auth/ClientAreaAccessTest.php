<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Личный кабинет закрыт для сотрудников, а вход отправляет их
 * в админку — даже если в сессии остался адрес клиентской страницы.
 */
class ClientAreaAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_is_redirected_away_from_client_area(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get('/client/documents')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_staff_is_redirected_away_from_client_area(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Staff]);

        $this->actingAs($staff)
            ->get('/client/dashboard')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_applicant_can_open_client_area(): void
    {
        $user = User::factory()->create(['role' => UserRole::Applicant]);

        $this->actingAs($user)
            ->get('/client/documents')
            ->assertOk();
    }

    public function test_admin_login_ignores_remembered_client_page(): void
    {
        $admin = User::factory()->create([
            'role'     => UserRole::Admin,
            'password' => bcrypt('password'),
        ]);

        // Так ведёт себя браузер: сначала попытка открыть клиентскую
        // страницу без входа, адрес запоминается в сессии, потом вход.
        $this->get('/client/documents');

        $this->post('/login', [
            'email'    => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.clients.index'));
    }
}