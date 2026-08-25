<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Аудит, Проблема №15 — две отдельные причины одного и того же симптома.
 *
 * 1) Пробелы вокруг ':' в 'in                 : admin,staff' и
 *    'unique: users' ломали разбор параметров — role=admin никогда не
 *    проходил in:, а unique:users молча проверял несуществующую таблицу
 *    ' users' и поэтому ВСЕГДА пропускал email как "свободный".
 * 2) Отдельно, уже после фикса (1): StaffController::store() использовал
 *    обычный User::create([...'role' => ...]) — а role/permissions
 *    намеренно исключены из User::$fillable (защита от mass assignment).
 *    Из-за этого пользователь реально создавался (честный успех), но
 *    всегда с ролью по умолчанию 'client', какая бы роль ни была выбрана
 *    в форме — в списке "Сотрудники" такой пользователь просто не виден.
 */
class StoreStaffRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    // --- (1) сами правила валидации ---

    public function test_admin_role_passes_in_rule(): void
    {
        $validator = Validator::make([
            'name' => 'Новый Админ',
            'email' => 'new-admin@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ], (new StoreStaffRequest())->rules());

        $this->assertFalse($validator->errors()->has('role'));
    }

    public function test_staff_role_passes_in_rule(): void
    {
        $validator = Validator::make([
            'name' => 'Новый Сотрудник',
            'email' => 'new-staff@example.com',
            'password' => 'password123',
            'role' => 'staff',
        ], (new StoreStaffRequest())->rules());

        $this->assertFalse($validator->errors()->has('role'));
    }

    public function test_invalid_role_still_fails_validation(): void
    {
        $validator = Validator::make([
            'name' => 'Кто-то',
            'email' => 'someone@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
        ], (new StoreStaffRequest())->rules());

        $this->assertTrue($validator->errors()->has('role'));
    }

    public function test_duplicate_email_fails_unique_rule(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $validator = Validator::make([
            'name' => 'Дубликат',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'role' => 'staff',
        ], (new StoreStaffRequest())->rules());

        $this->assertTrue($validator->errors()->has('email'));
    }

    public function test_free_email_passes_unique_rule(): void
    {
        $validator = Validator::make([
            'name' => 'Свободный Email',
            'email' => 'free@example.com',
            'password' => 'password123',
            'role' => 'staff',
        ], (new StoreStaffRequest())->rules());

        $this->assertFalse($validator->errors()->has('email'));
    }

    // --- (2) реальное создание через контроллер: роль должна СОХРАНИТЬСЯ ---

    public function test_admin_can_create_staff_with_admin_role_end_to_end(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'Второй Админ',
            'email' => 'second-admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
            'permissions' => [],
        ]);

        $response->assertSessionDoesntHaveErrors();

        // Именно это раньше молча ломалось: строка создавалась, но с
        // role='client' по умолчанию вместо выбранной в форме.
        $this->assertDatabaseHas('users', [
            'email' => 'second-admin@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_created_staff_permissions_are_actually_saved(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'Сотрудник С Правами',
            'email' => 'staff-with-perms@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff',
            'permissions' => ['clients', 'tickets'],
        ]);

        $created = User::where('email', 'staff-with-perms@example.com')->first();

        $this->assertNotNull($created);
        $this->assertEquals(['clients', 'tickets'], $created->permissions);
    }
}