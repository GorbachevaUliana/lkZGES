<?php

namespace Tests\Feature\Draft;

use App\Enums\UserRole;
use App\Models\ApplicationTemplate;
use App\Models\User;
use App\Services\DraftApplicationService;
use Database\Seeders\ApplicationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedDraftPropTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationTemplateSeeder::class);
    }

    public function test_draft_is_shared_on_dashboard(): void
    {
        $user     = User::factory()->create(['role' => UserRole::Applicant]);
        $template = ApplicationTemplate::where('slug', 'application-individual')->firstOrFail();
        app(DraftApplicationService::class)->getOrCreateForUser($user, $template);

        $response = $this->actingAs($user)->get(route('client.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('draft.client_type', 'individual')
        );
    }

    public function test_draft_is_null_when_none(): void
    {
        $user = User::factory()->create(['role' => UserRole::Applicant]);

        $response = $this->actingAs($user)->get(route('client.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('draft', null)
        );
    }
}