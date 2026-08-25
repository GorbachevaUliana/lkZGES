<?php

namespace Tests\Feature\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\ClientType;
use App\Enums\PropertyStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\Client;
use App\Models\Property;
use App\Models\Tariff;
use App\Models\User;
use App\Services\ApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Найдено 24.08 при проверке: заявку нельзя было одобрить напрямую из
 * "Ожидает", не пройдя через "В работе" — а на практике заявку иногда
 * рассматривают и одобряют быстро, без отдельного шага "взять в работу".
 * ApplicationService::ALLOWED_TRANSITIONS теперь разрешает оба пути:
 * Pending -> Approved напрямую, и Pending -> Processing -> Approved.
 */
class ApplicationStatusTransitionsTest extends TestCase
{
    use RefreshDatabase;

    private function makePendingApplication(): array
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $applicantUser = User::factory()->create(['role' => UserRole::Applicant]);
        $client = Client::create([
            'user_id' => $applicantUser->id,
            'client_type' => ClientType::Individual->value,
            'last_name' => 'Заявителев',
        ]);
        $property = Property::create([
            'client_id' => $client->id,
            'address' => 'Тестовый адрес заявки',
            'status' => PropertyStatus::Pending->value,
        ]);
        $tariff = Tariff::create(['name' => 'Тестовый тариф', 'price_1' => 5, 'price_2' => 6, 'price_3' => 7, 'starts_at' => now()->subYear()]);
        // applications.template_id — NOT NULL, забыла в первой версии теста.
        $template = ApplicationTemplate::create([
            'title' => 'Тестовый шаблон',
            'slug' => 'test-template-' . uniqid(),
            'client_type' => ClientType::Individual->value,
            'content' => [],
            'is_active' => true,
        ]);

        $application = Application::create([
            'user_id' => $applicantUser->id,
            'client_id' => $client->id,
            'template_id' => $template->id,
            'client_type' => ClientType::Individual->value,
            'property_id' => $property->id,
            'status' => ApplicationStatus::Pending->value,
            'data' => [],
        ]);

        return [$admin, $application, $property, $tariff];
    }

    public function test_pending_can_be_approved_directly(): void
    {
        [$admin, $application, , $tariff] = $this->makePendingApplication();

        $response = $this->actingAs($admin)->post("/admin/applications/{$application->id}/status", [
            'status' => 'approved',
            'account_number' => '900123',
            'tariff_id' => $tariff->id,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertEquals('approved', $application->fresh()->status);
    }

    public function test_pending_can_still_go_through_processing_first(): void
    {
        [$admin, $application] = $this->makePendingApplication();

        $response = $this->actingAs($admin)->post("/admin/applications/{$application->id}/status", [
            'status' => 'processing',
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertEquals('processing', $application->fresh()->status);
    }

    public function test_new_cannot_be_approved_directly(): void
    {
        // Контрольная проверка "в другую сторону" — расширение перехода
        // для Pending не должно было случайно ослабить проверку для New.
        $service = new ApplicationService();

        $this->assertFalse($service->canTransition(ApplicationStatus::New->value, ApplicationStatus::Approved->value));
        $this->assertTrue($service->canTransition(ApplicationStatus::Pending->value, ApplicationStatus::Approved->value));
        $this->assertTrue($service->canTransition(ApplicationStatus::Pending->value, ApplicationStatus::Processing->value));
    }

    public function test_approved_application_cannot_be_moved_again(): void
    {
        $service = new ApplicationService();

        $this->assertFalse($service->canTransition(ApplicationStatus::Approved->value, ApplicationStatus::Pending->value));
        $this->assertFalse($service->canTransition(ApplicationStatus::Approved->value, ApplicationStatus::Rejected->value));
    }
}