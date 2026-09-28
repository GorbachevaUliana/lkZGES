<?php

namespace Tests\Feature\Contract;

use App\Enums\ApplicationStatus;
use App\Enums\ContractStatus;
use App\Enums\SignatureMethod;
use App\Enums\SignerType;
use App\Enums\UserRole;
use App\Mail\ContractSigningCode;
use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractPepCode;
use App\Models\User;
use App\Services\ContractPepSigningService;
use App\Services\ContractService;
use Database\Seeders\ApplicationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ContractPepSigningTest extends TestCase
{
    use RefreshDatabase;

    private ContractPepSigningService $pep;
    private ContractService $contracts;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationTemplateSeeder::class);
        Storage::fake('local');
        Mail::fake();
        config(['contracts.signing_power_threshold_kw' => 670]);

        $this->pep       = app(ContractPepSigningService::class);
        $this->contracts = app(ContractService::class);
    }

    /**
     * Договор физлица, доведённый до ожидания подписи клиента.
     */
    private function awaitingContract(): Contract
    {
        $this->user = User::factory()->create(['role' => UserRole::Applicant]);

        $client = Client::create([
            'user_id'     => $this->user->id,
            'client_type' => 'individual',
            'last_name'   => 'Иванов',
            'first_name'  => 'Иван',
            'phone'       => '89000000000',
            'email'       => 'ivan@example.test',
        ]);

        $application = Application::create([
            'user_id'      => $this->user->id,
            'client_id'    => $client->id,
            'template_id'  => ApplicationTemplate::where('slug', 'application-individual')->firstOrFail()->id,
            'client_type'  => 'individual',
            'data'         => [],
            'status'       => ApplicationStatus::Approved->value,
            'max_power_kw' => 700.0,
        ]);

        $contract = $this->contracts->createFromUpload(
            $application,
            UploadedFile::fake()->create('dogovor.pdf', 50, 'application/pdf')
        );

        $this->contracts->attachOrganizationSignature(
            $contract,
            UploadedFile::fake()->create('dogovor.pdf.sig', 2),
            User::factory()->create(['role' => UserRole::Staff]),
        );

        return $this->contracts->publish($contract->fresh());
    }

    /**
     * Код известен только из письма, поэтому в тестах подменяем хеш
     * на заранее известный — иначе подтвердить его нечем.
     */
    private function forceCode(Contract $contract, string $code = '123456'): ContractPepCode
    {
        $row = $contract->pepCodes()->latest('id')->firstOrFail();
        $row->update(['code_hash' => Hash::make($code)]);

        return $row->fresh();
    }

    // ==================== ВЫДАЧА КОДА ====================

    public function test_code_is_sent_to_client_email(): void
    {
        $contract = $this->awaitingContract();

        $row = $this->pep->sendCode($contract, $this->user);

        $this->assertSame('ivan@example.test', $row->sent_to);
        $this->assertSame('email', $row->channel);
        $this->assertTrue($row->isUsable());
        Mail::assertSent(ContractSigningCode::class);
    }

    public function test_second_code_request_is_throttled(): void
    {
        $contract = $this->awaitingContract();
        $this->pep->sendCode($contract, $this->user);

        $this->expectException(ValidationException::class);
        $this->pep->sendCode($contract->fresh(), $this->user);
    }

    public function test_stranger_cannot_request_code(): void
    {
        $contract = $this->awaitingContract();
        $stranger = User::factory()->create(['role' => UserRole::Applicant]);

        $this->expectException(ValidationException::class);
        $this->pep->sendCode($contract, $stranger);
    }

    // ==================== ПОДПИСАНИЕ ====================

    public function test_correct_code_signs_and_activates_contract(): void
    {
        $contract = $this->awaitingContract();
        $this->pep->sendCode($contract, $this->user);
        $this->forceCode($contract);

        $signature = $this->pep->confirm($contract->fresh(), $this->user, '123456', '10.0.0.1', 'TestBrowser');

        $this->assertSame(SignerType::Client->value, $signature->signer);
        $this->assertSame(SignatureMethod::Pep->value, $signature->method);
        $this->assertSame($contract->file_hash, $signature->document_hash);
        $this->assertSame('ivan@example.test', $signature->pep_sent_to);
        $this->assertSame('10.0.0.1', $signature->ip);

        $fresh = $contract->fresh();
        $this->assertSame(ContractStatus::Active->value, $fresh->status);
        $this->assertNotNull($fresh->signed_at);
    }

    public function test_wrong_code_is_rejected_and_counted(): void
    {
        $contract = $this->awaitingContract();
        $this->pep->sendCode($contract, $this->user);
        $row = $this->forceCode($contract);

        try {
            $this->pep->confirm($contract->fresh(), $this->user, '000000');
            $this->fail('Неверный код должен отклоняться');
        } catch (ValidationException) {
            // ожидаемо
        }

        $this->assertSame(1, $row->fresh()->attempts);
        $this->assertSame(ContractStatus::AwaitingClient->value, $contract->fresh()->status);
    }

    public function test_code_is_blocked_after_too_many_attempts(): void
    {
        $contract = $this->awaitingContract();
        $this->pep->sendCode($contract, $this->user);
        $row = $this->forceCode($contract);

        $row->update(['attempts' => 5]);

        // Даже верный код больше не принимается
        $this->expectException(ValidationException::class);
        $this->pep->confirm($contract->fresh(), $this->user, '123456');
    }

    public function test_expired_code_is_rejected(): void
    {
        $contract = $this->awaitingContract();
        $this->pep->sendCode($contract, $this->user);
        $row = $this->forceCode($contract);

        $row->update(['expires_at' => now()->subMinute()]);

        $this->expectException(ValidationException::class);
        $this->pep->confirm($contract->fresh(), $this->user, '123456');
    }

    public function test_code_cannot_be_used_twice(): void
    {
        $contract = $this->awaitingContract();
        $this->pep->sendCode($contract, $this->user);
        $this->forceCode($contract);

        $this->pep->confirm($contract->fresh(), $this->user, '123456');

        $this->expectException(ValidationException::class);
        $this->pep->confirm($contract->fresh(), $this->user, '123456');
    }

    public function test_tampered_file_cannot_be_signed(): void
    {
        $contract = $this->awaitingContract();
        $this->pep->sendCode($contract, $this->user);
        $this->forceCode($contract);

        Storage::disk('local')->put($contract->file_path, 'подменённый договор');

        $this->expectException(ValidationException::class);
        $this->pep->confirm($contract->fresh(), $this->user, '123456');
    }

    public function test_stranger_cannot_sign_with_stolen_code(): void
    {
        $contract = $this->awaitingContract();
        $this->pep->sendCode($contract, $this->user);
        $this->forceCode($contract);

        $stranger = User::factory()->create(['role' => UserRole::Applicant]);

        $this->expectException(ValidationException::class);
        $this->pep->confirm($contract->fresh(), $stranger, '123456');
    }
}