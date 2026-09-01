<?php

namespace Tests\Feature\Account;

use App\Mail\AccountLinkCode;
use App\Models\Client;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AccountLinkEmailFallbackTest extends TestCase
{
    use RefreshDatabase;

    private function makeMatchingClient(?string $email): void
    {
        $client = Client::create([
            'client_type' => 'individual',
            'last_name'   => 'Иванов',
            'first_name'  => 'Иван',
            'middle_name' => 'Иванович',
            'email'       => $email,
        ]);

        Property::create([
            'client_id'      => $client->id,
            'account_number' => '100500',
            'address'        => 'г. Заринск, ул. Тестовая, д. 1',
            'status'         => 'active',
        ]);
    }

    private array $payload = [
        'account_number' => '100500',
        'last_name'      => 'Иванов',
        'first_name'     => 'Иван',
        'middle_name'    => 'Иванович',
    ];
    public function test_no_code_sent_when_client_has_no_email(): void
    {
        Mail::fake();
        $this->makeMatchingClient(null);

        $user = User::factory()->create(['email' => 'attacker@example.com']);

        $this->actingAs($user)->post(route('account.link'), $this->payload);

        Mail::assertNothingSent();                    // код никому не ушёл
        $this->assertNull($user->fresh()->link_code); // код даже не сгенерирован
    }

    public function test_code_sent_only_to_client_email(): void
    {
        Mail::fake();
        $this->makeMatchingClient('client@example.com');

        $user = User::factory()->create(['email' => 'registrant@example.com']);

        $this->actingAs($user)->post(route('account.link'), $this->payload);

        Mail::assertSent(AccountLinkCode::class, function ($mail) {
            return $mail->hasTo('client@example.com')
                && ! $mail->hasTo('registrant@example.com');
        });
        $this->assertNotNull($user->fresh()->link_code);
    }
}