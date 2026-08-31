<?php

namespace Tests\Feature\Draft;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\User;
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
}