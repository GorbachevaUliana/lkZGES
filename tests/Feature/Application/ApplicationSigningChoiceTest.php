<?php

namespace Tests\Feature\Application;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Выбор подписания в форме заявки.
 *
 * Шаблон создаётся здесь же, минимальный: одно поле мощности, без файлов.
 * Так тест проверяет только выбор подписания и не ломается, когда
 * меняется состав настоящей формы.
 */
class ApplicationSigningChoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['contracts.signing_power_threshold_kw' => 670]);

        ApplicationTemplate::create([
            'title'       => 'Тестовая заявка',
            'slug'        => 'test-individual',
            'client_type' => 'individual',
            'is_active'   => true,
            'content'     => [
                ['type' => 'input_field', 'data' => ['key' => 'max_power', 'label' => 'Мощность, кВт']],
            ],
        ]);

        $this->user = User::factory()->create(['role' => UserRole::Guest]);
    }

    private function submit(array $fields)
    {
        return $this->actingAs($this->user)->post(
            route('application.store', 'test-individual'),
            array_merge(['last_name' => 'Иванов', 'first_name' => 'Иван'], $fields)
        );
    }

    private function submittedApplication(): ?Application
    {
        return Application::where('user_id', $this->user->id)
            ->where('status', ApplicationStatus::Pending->value)
            ->first();
    }

    public function test_client_choice_to_sign_is_saved(): void
    {
        $this->submit(['max_power' => '15', 'signing_requested' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertTrue($this->submittedApplication()->signing_requested);
    }

    public function test_client_choice_not_to_sign_is_saved(): void
    {
        $this->submit(['max_power' => '15', 'signing_requested' => '0'])
            ->assertSessionHasNoErrors();

        $this->assertFalse($this->submittedApplication()->signing_requested);
    }

    public function test_application_without_choice_is_rejected(): void
    {
        $this->submit(['max_power' => '15'])
            ->assertSessionHasErrors('signing_requested');

        $this->assertNull($this->submittedApplication(), 'Заявка не должна создаться без выбора');
    }

    public function test_choice_is_not_required_above_threshold(): void
    {
        $this->submit(['max_power' => '700'])
            ->assertSessionHasNoErrors();

        $this->assertNull($this->submittedApplication()->signing_requested);
    }

    public function test_client_cannot_decline_signing_above_threshold(): void
    {
        $this->submit(['max_power' => '700', 'signing_requested' => '0'])
            ->assertSessionHasNoErrors();

        // При такой мощности выбор не читается вовсе — даже если его прислали
        $this->assertNull($this->submittedApplication()->signing_requested);
    }

    public function test_power_with_comma_counts_for_threshold(): void
    {
        $this->submit(['max_power' => '670,0'])
            ->assertSessionHasNoErrors();

        $application = $this->submittedApplication();

        $this->assertSame(670.0, $application->max_power_kw);
        $this->assertNull($application->signing_requested);
    }

    public function test_choice_does_not_leak_into_form_data(): void
    {
        $this->submit(['max_power' => '15', 'signing_requested' => '1']);

        $this->assertArrayNotHasKey('signing_requested', $this->submittedApplication()->data);
    }
}