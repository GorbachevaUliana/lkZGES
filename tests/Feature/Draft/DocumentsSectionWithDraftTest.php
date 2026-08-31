<?php

namespace Tests\Feature\Draft;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\ApplicationTemplate;
use App\Models\User;
use App\Services\DraftApplicationService;
use Database\Seeders\ApplicationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentsSectionWithDraftTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationTemplateSeeder::class);
    }

    public function test_documents_shows_draft_without_redirect(): void
    {
        $user = User::factory()->create(['role' => UserRole::Applicant]);
        $template = ApplicationTemplate::where('slug', 'application-individual')->firstOrFail();

        app(DraftApplicationService::class)->getOrCreateForUser($user, $template);
        $this->assertNull($user->client);

        $response = $this->actingAs($user)->get(route('client.documents'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Client/Documents')
            ->has('draft')
            ->where('draft.status', ApplicationStatus::Draft->value)
        );
    }
}