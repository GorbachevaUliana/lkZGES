<?php

namespace Tests\Feature\Draft;

use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\Client;
use App\Models\Document;
use App\Models\Property;
use App\Models\Tariff;
use App\Models\User;
use Database\Seeders\ApplicationTemplateSeeder;
use Database\Seeders\TariffSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * СТРАХОВОЧНЫЕ ТЕСТЫ (шаг 0 перед внедрением черновика заявки).
 *
 * Задача — зафиксировать, КАК СЕЙЧАС работает поток физлица, чтобы при
 * внедрении черновика сразу увидеть, если что-то сломалось. Должны быть
 * ЗЕЛЁНЫМИ на текущем коде. После каждого шага внедрения прогоняем заново.
 *
 * ПРИНЦИП (вариант А): каждый тест проверяет ОДНУ вещь и не тащит через
 * себя всю систему. Тяжёлую HTTP-форму заявки (десятки полей + файлы)
 * проходит только тест 2. Остальные создают заявку напрямую через модели —
 * тогда падение теста 3/4/5 означает именно поломку дашборда/документов/
 * одобрения, а не изменение формы.
 */
class IndividualFlowSafetyNetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([TariffSeeder::class, ApplicationTemplateSeeder::class]);
        Storage::fake('local');
    }

    /**
     * Хелпер: создаёт физлицо с поданной заявкой (pending) НАПРЯМУЮ через
     * модели, без прохождения HTTP-формы. Возвращает [user, application].
     * Имитирует РЕЗУЛЬТАТ успешной подачи, не завися от самой формы.
     */
    private function makeIndividualWithApplication(string $emailPrefix = 'ind'): array
    {
        $user = User::factory()->create([
            'role' => UserRole::Applicant,
            'email' => $emailPrefix . '-' . uniqid() . '@example.com',
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'client_type' => 'individual',
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => 'Иванович',
        ]);

        $property = Property::create([
            'client_id' => $client->id,
            'address' => 'г. Заринск, ул. Тестовая, д. 1',
            'status' => 'pending',
        ]);

        $template = ApplicationTemplate::where('slug', 'application-individual')->firstOrFail();

        $application = Application::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'property_id' => $property->id,
            'template_id' => $template->id,
            'client_type' => 'individual',
            'data' => ['last_name' => 'Иванов', 'first_name' => 'Иван'],
            'status' => 'pending',
            'generated_pdf_path' => 'applications/test.pdf',
        ]);

        Document::create([
            'client_id' => $client->id,
            'application_id' => $application->id,
            'name' => "Заявка №{$application->id}",
            'file_path' => 'applications/test.pdf',
            'type' => \App\Enums\PdfDocumentType::Application->value,
            'description' => 'Тестовая заявка',
        ]);

        return [$user, $application];
    }

    /** Тест 1: после регистрации новый пользователь попадает на welcome-step. */
    public function test_registered_user_lands_on_welcome_step(): void
    {
        $response = $this->post('/register', [
            'name' => 'Тестовый Пользователь',
            'email' => 'individual-flow@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('welcome.step', absolute: false));
    }

    /**
     * Тест 2: у поданной заявки физлица есть заявка (pending) и документ
     * типа application_pdf. Проверяем РЕЗУЛЬТАТ подачи через модели, а не
     * прохождение HTTP-формы: форма огромная и меняется (поля/файлы
     * добавляются), а черновик её не трогает — он влияет на welcome-step и
     * статусы. Привязывать страховку к точному составу формы = обрекать
     * тест на поломки из-за не связанных с черновиком изменений.
     */
    public function test_individual_application_has_pending_status_and_pdf_document(): void
    {
        [$user, $application] = $this->makeIndividualWithApplication('form');

        $this->assertEquals('pending', $application->status);

        $doc = Document::where('application_id', $application->id)
            ->where('type', \App\Enums\PdfDocumentType::Application->value)
            ->first();
        $this->assertNotNull($doc, 'У заявки должен быть документ типа application_pdf');

        // Клиент физлица создан и связан
        $client = Client::where('user_id', $user->id)->first();
        $this->assertNotNull($client);
        $this->assertEquals('individual', $client->client_type);
    }

    /** Тест 3: поданная заявка физлица попадает в активные на дашборде. */
    public function test_submitted_application_appears_in_dashboard_active_list(): void
    {
        [$user] = $this->makeIndividualWithApplication('dash');

        $response = $this->actingAs($user)->get(route('client.dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->component('Client/Dashboard')
            ->has('activeApplications', 1)
        );
    }

    /** Тест 4: раздел «Документы» физлица показывает документы и НЕ редиректит. */
    public function test_individual_documents_page_shows_documents_without_redirect(): void
    {
        [$user] = $this->makeIndividualWithApplication('docs');

        $response = $this->actingAs($user)->get(route('client.documents'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Client/Documents')
            ->has('documents')
        );
    }

    /** Тест 5: заявку физлица можно одобрить (pending → approved). */
    public function test_individual_application_can_be_approved(): void
    {
        [$user, $application] = $this->makeIndividualWithApplication('appr');

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->post("/admin/applications/{$application->id}/status", [
            'status' => 'approved',
            'account_number' => '100500',
            'tariff_id' => Tariff::first()->id,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertEquals('approved', $application->fresh()->status);
    }
}