<?php

namespace Tests\Feature\Draft;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\User;
use App\Models\Client;
use App\Models\Property;
use App\Services\DraftApplicationService;
use Database\Seeders\ApplicationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DraftApplicationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationTemplateSeeder::class);
    }

    private function individualTemplate(): ApplicationTemplate
    {
        return ApplicationTemplate::where('slug', 'application-individual')->firstOrFail();
    }

    public function test_creates_a_single_draft_when_none_exists(): void
    {
        $user = User::factory()->create(['role' => UserRole::Applicant]);
        $template = $this->individualTemplate();

        $draft = app(DraftApplicationService::class)->getOrCreateForUser($user, $template);

        $this->assertEquals(ApplicationStatus::Draft->value, $draft->status);
        $this->assertEquals($user->id, $draft->user_id);
        $this->assertEquals($template->id, $draft->template_id);
        $this->assertEquals($template->client_type, $draft->client_type);
        $this->assertNull($draft->client_id);
        $this->assertNull($draft->property_id);

        $this->assertEquals(1, Application::where('user_id', $user->id)->count());
    }

    public function test_reuses_existing_draft_on_second_call(): void
    {
        $user = User::factory()->create(['role' => UserRole::Applicant]);
        $template = $this->individualTemplate();
        $service = app(DraftApplicationService::class);

        $first = $service->getOrCreateForUser($user, $template);
        $second = $service->getOrCreateForUser($user, $template);

        $this->assertEquals($first->id, $second->id);
        $this->assertEquals(
            1,
            Application::where('user_id', $user->id)
                ->where('status', ApplicationStatus::Draft->value)
                ->count()
        );
    }

        /** Смена типа: тот же черновик меняет client_type и шаблон, второй не создаётся. */
    public function test_switching_type_updates_the_same_draft(): void
    {
        $user       = User::factory()->create(['role' => UserRole::Applicant]);
        $individual = ApplicationTemplate::where('slug', 'application-individual')->firstOrFail();
        $legal      = ApplicationTemplate::where('slug', 'application-legal')->firstOrFail();
        $service    = app(DraftApplicationService::class);

        $draft = $service->getOrCreateForUser($user, $individual);
        $this->assertEquals('individual', $draft->client_type);

        $switched = $service->getOrCreateForUser($user, $legal);

        // Тот же черновик, но тип и шаблон обновились
        $this->assertEquals($draft->id, $switched->id);
        $this->assertEquals('legal', $switched->client_type);
        $this->assertEquals($legal->id, $switched->template_id);

        // По-прежнему ровно один черновик у пользователя
        $this->assertEquals(
            1,
            Application::where('user_id', $user->id)
                ->where('status', ApplicationStatus::Draft->value)
                ->count()
        );
    }

        /** finalize: существующий черновик превращается в pending, тем же id. */
    public function test_finalize_converts_existing_draft_to_pending(): void
    {
        $user     = User::factory()->create(['role' => UserRole::Applicant]);
        $template = $this->individualTemplate();
        $service  = app(DraftApplicationService::class);

        // сперва появляется черновик (как на шаге 2b)
        $draft = $service->getOrCreateForUser($user, $template);

        // клиент и объект, которые submit подготовил бы к этому моменту
        $client = Client::create([
            'user_id'     => $user->id,
            'client_type' => 'individual',
            'last_name'   => 'Иванов',
            'first_name'  => 'Иван',
        ]);
        $property = Property::create([
            'client_id' => $client->id,
            'address'   => 'г. Заринск, ул. Тестовая, д. 1',
            'status'    => 'pending',
        ]);

        $application = $service->finalizeForUser($user, [
            'client_id'   => $client->id,
            'property_id' => $property->id,
            'template_id' => $template->id,
            'client_type' => 'individual',
            'data'        => ['last_name' => 'Иванов', 'first_name' => 'Иван'],
        ]);

        // тот же черновик, теперь pending и со связями
        $this->assertEquals($draft->id, $application->id);
        $this->assertEquals(ApplicationStatus::Pending->value, $application->status);
        $this->assertEquals($client->id, $application->client_id);
        $this->assertEquals($property->id, $application->property_id);

        // черновиков не осталось, заявка одна
        $this->assertEquals(0, Application::where('user_id', $user->id)
            ->where('status', ApplicationStatus::Draft->value)->count());
        $this->assertEquals(1, Application::where('user_id', $user->id)->count());
    }

    /** finalize без черновика создаёт pending-заявку (запасной путь). */
    public function test_finalize_creates_pending_when_no_draft(): void
    {
        $user     = User::factory()->create(['role' => UserRole::Applicant]);
        $template = $this->individualTemplate();
        $service  = app(DraftApplicationService::class);

        $application = $service->finalizeForUser($user, [
            'template_id' => $template->id,
            'client_type' => 'individual',
            'data'        => [],
        ]);

        $this->assertEquals(ApplicationStatus::Pending->value, $application->status);
        $this->assertEquals($user->id, $application->user_id);
        $this->assertEquals(1, Application::where('user_id', $user->id)->count());
    }
}