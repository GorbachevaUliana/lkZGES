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

class DraftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationTemplateSeeder::class);
    }

    private function makeDraftFor(User $user): void
    {
        $template = ApplicationTemplate::where('slug', 'application-individual')->firstOrFail();
        app(DraftApplicationService::class)->getOrCreateForUser($user, $template);
    }

    private function draftCount(User $user): int
    {
        return Application::where('user_id', $user->id)
            ->where('status', ApplicationStatus::Draft->value)
            ->count();
    }

    public function test_user_can_delete_own_draft(): void
    {
        $user = User::factory()->create(['role' => UserRole::Applicant]);
        $this->makeDraftFor($user);
        $this->assertEquals(1, $this->draftCount($user));
        $this->actingAs($user)->delete(route('client.draft.destroy'));
        $this->assertEquals(0, $this->draftCount($user));
    }

    public function test_deleting_own_draft_does_not_touch_another_users_draft(): void
    {
        $alice = User::factory()->create(['role' => UserRole::Applicant]);
        $bob = User::factory()->create(['role' => UserRole::Applicant]);
        $this->makeDraftFor($alice);
        $this->makeDraftFor($bob);
        $this->actingAs($alice)->delete(route('client.draft.destroy'));
        $this->assertEquals(0, $this->draftCount($alice));
        $this->assertEquals(1, $this->draftCount($bob));
    }
}