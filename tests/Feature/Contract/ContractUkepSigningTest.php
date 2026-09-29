<?php

namespace Tests\Feature\Contract;

use App\Enums\ApplicationStatus;
use App\Enums\ContractStatus;
use App\Enums\SignatureMethod;
use App\Enums\SignerType;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\Client;
use App\Models\Contract;
use App\Models\User;
use App\Services\ContractService;
use App\Services\ContractUkepSigningService;
use Database\Seeders\ApplicationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ContractUkepSigningTest extends TestCase
{
    use RefreshDatabase;

    private ContractUkepSigningService $ukep;
    private ContractService $contracts;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationTemplateSeeder::class);
        Storage::fake('local');
        config(['contracts.signing_power_threshold_kw' => 670]);

        $this->ukep      = app(ContractUkepSigningService::class);
        $this->contracts = app(ContractService::class);
    }

    /**
     * Договор, доведённый до ожидания подписи клиента.
     * Тип лица решает, каким способом клиент будет подписывать.
     */
    private function awaitingContract(string $clientType = 'legal'): Contract
    {
        $this->user = User::factory()->create(['role' => UserRole::Applicant]);

        $client = Client::create([
            'user_id'     => $this->user->id,
            'client_type' => $clientType,
            'last_name'   => 'Иванов',
            'first_name'  => 'Иван',
            'phone'       => '89000000000',
            'email'       => 'ivan@example.test',
        ]);

        $slug = $clientType === 'legal' ? 'application-legal' : 'application-individual';

        $application = Application::create([
            'user_id'      => $this->user->id,
            'client_id'    => $client->id,
            'template_id'  => ApplicationTemplate::where('slug', $slug)->firstOrFail()->id,
            'client_type'  => $clientType,
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

    private function sig(string $name = 'client.sig'): UploadedFile
    {
        return UploadedFile::fake()->create($name, 2);
    }

    public function test_signature_signs_and_activates_contract(): void
    {
        $contract = $this->awaitingContract();

        $signature = $this->ukep->sign($contract, $this->user, $this->sig(), '10.0.0.1', 'TestBrowser');

        $this->assertSame(SignerType::Client->value, $signature->signer);
        $this->assertSame(SignatureMethod::Ukep->value, $signature->method);
        $this->assertSame($contract->file_hash, $signature->document_hash);
        $this->assertSame('10.0.0.1', $signature->ip);
        $this->assertTrue(Storage::disk('local')->exists($signature->signature_file_path));

        $fresh = $contract->fresh();
        $this->assertSame(ContractStatus::Active->value, $fresh->status);
        $this->assertNotNull($fresh->signed_at);
    }

    public function test_organization_signature_survives_client_signing(): void
    {
        $contract = $this->awaitingContract();

        $this->ukep->sign($contract, $this->user, $this->sig());

        $fresh = $contract->fresh();
        $this->assertNotNull($fresh->organizationSignature()->first());
        $this->assertNotNull($fresh->clientSignature()->first());
    }

    public function test_stranger_cannot_sign(): void
    {
        $contract = $this->awaitingContract();
        $stranger = User::factory()->create(['role' => UserRole::Applicant]);

        $this->expectException(ValidationException::class);
        $this->ukep->sign($contract, $stranger, $this->sig());
    }

    public function test_contract_cannot_be_signed_twice(): void
    {
        $contract = $this->awaitingContract();
        $this->ukep->sign($contract, $this->user, $this->sig());

        $this->expectException(ValidationException::class);
        $this->ukep->sign($contract->fresh(), $this->user, $this->sig('again.sig'));
    }

    public function test_individual_contract_cannot_be_signed_with_ukep(): void
    {
        $contract = $this->awaitingContract('individual');

        $this->expectException(ValidationException::class);
        $this->ukep->sign($contract, $this->user, $this->sig());
    }

    public function test_tampered_file_cannot_be_signed(): void
    {
        $contract = $this->awaitingContract();

        Storage::disk('local')->put($contract->file_path, 'подменённый договор');

        $this->expectException(ValidationException::class);
        $this->ukep->sign($contract, $this->user, $this->sig());
    }
}