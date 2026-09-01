<?php

namespace Tests\Feature\Draft;

use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\User;
use App\Services\DraftApplicationService;
use Database\Seeders\ApplicationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DraftAutosaveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationTemplateSeeder::class);
    }

    private function makeDraftFor(User $user): Application
    {
        $template = ApplicationTemplate::where('slug', 'application-individual')->firstOrFail();
        return app(DraftApplicationService::class)->getOrCreateForUser($user, $template);
    }

    public function test_saves_data_into_draft(): void
    {
        $user  = User::factory()->create(['role' => UserRole::Applicant]);
        $draft = $this->makeDraftFor($user);

        $payload = ['last_name' => 'Иванов', 'first_name' => 'Иван'];

        $this->actingAs($user)
            ->patch(route('client.draft.save'), ['data' => $payload])
            ->assertOk();

        $this->assertEquals($payload, $draft->fresh()->data);
    }

    public function test_save_is_scoped_to_own_draft(): void
    {
        $alice = User::factory()->create(['role' => UserRole::Applicant]);
        $bob   = User::factory()->create(['role' => UserRole::Applicant]);
        $this->makeDraftFor($alice);
        $bobDraft = $this->makeDraftFor($bob);

        $this->actingAs($alice)
            ->patch(route('client.draft.save'), ['data' => ['last_name' => 'Алисина']])
            ->assertOk();

        $this->assertEquals([], $bobDraft->fresh()->data);
    }

    public function test_save_without_draft_is_noop(): void
    {
        $user = User::factory()->create(['role' => UserRole::Applicant]);

        $this->actingAs($user)
            ->patch(route('client.draft.save'), ['data' => ['x' => 'y']])
            ->assertOk();

        $this->assertEquals(0, Application::where('user_id', $user->id)->count());
    }

    public function test_show_passes_draft_data_to_form(): void
    {
        $user  = User::factory()->create(['role' => UserRole::Applicant]);
        $draft = $this->makeDraftFor($user);
        $draft->update(['data' => ['last_name' => 'Иванов']]);

        $response = $this->actingAs($user)
            ->get(route('application.show', ['slug' => 'application-individual']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Applications/DynamicForm')
            ->where('draftData.last_name', 'Иванов')
        );
    }
}