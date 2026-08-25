<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Http\Requests\Admin\UploadApplicationDocumentRequest;
use App\Http\Requests\Admin\UploadClientFileRequest;
use App\Http\Requests\Admin\UploadContractRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Аудит, Проблема №8.
 *
 * authorize() во всех трёх Request сравнивал auth()->user()->role
 * (backed enum UserRole) со строками через in_array(['admin', 'staff']).
 * Сравнение enum-объекта со строкой в PHP никогда не совпадает, поэтому
 * authorize() возвращал false для абсолютно любого пользователя, включая
 * админов — загрузка договоров, документов заявки и файлов клиента была
 * недоступна вообще никому.
 */
class UploadAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_is_authorized_to_upload_contract(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);

        $this->assertTrue((new UploadContractRequest())->authorize());
    }

    public function test_staff_is_authorized_to_upload_contract(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Staff]);
        $this->actingAs($staff);

        $this->assertTrue((new UploadContractRequest())->authorize());
    }

    public function test_admin_is_authorized_to_upload_application_document(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);

        $this->assertTrue((new UploadApplicationDocumentRequest())->authorize());
    }

    public function test_staff_is_authorized_to_upload_application_document(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Staff]);
        $this->actingAs($staff);

        $this->assertTrue((new UploadApplicationDocumentRequest())->authorize());
    }

    public function test_admin_is_authorized_to_upload_client_file(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);

        $this->assertTrue((new UploadClientFileRequest())->authorize());
    }

    public function test_staff_is_authorized_to_upload_client_file(): void
    {
        $staff = User::factory()->create(['role' => UserRole::Staff]);
        $this->actingAs($staff);

        $this->assertTrue((new UploadClientFileRequest())->authorize());
    }

    public function test_client_is_not_authorized_to_upload_client_file(): void
    {
        $client = User::factory()->create(['role' => UserRole::Client]);
        $this->actingAs($client);

        $this->assertFalse((new UploadClientFileRequest())->authorize());
    }
}