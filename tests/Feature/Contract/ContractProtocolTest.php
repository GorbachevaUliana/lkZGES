<?php

namespace Tests\Feature\Contract;

use App\Enums\ApplicationStatus;
use App\Enums\PdfDocumentType;
use App\Enums\SigningReason;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Document;
use App\Models\User;
use App\Services\ContractProtocolService;
use App\Services\ContractService;
use App\Services\ContractUkepSigningService;
use Database\Seeders\ApplicationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractProtocolTest extends TestCase
{
    use RefreshDatabase;

    private ContractProtocolService $protocols;
    private ContractService $contracts;
    private ContractUkepSigningService $ukep;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationTemplateSeeder::class);
        Storage::fake('local');
        config(['contracts.signing_power_threshold_kw' => 670]);

        $this->protocols = app(ContractProtocolService::class);
        $this->contracts = app(ContractService::class);
        $this->ukep      = app(ContractUkepSigningService::class);
    }

    private function awaitingContract(): Contract
    {
        $this->user = User::factory()->create(['role' => UserRole::Applicant]);

        $client = Client::create([
            'user_id'      => $this->user->id,
            'client_type'  => 'legal',
            'last_name'    => 'Иванов',
            'first_name'   => 'Иван',
            'company_name' => 'ООО «Тест»',
            'inn'          => '2205001234',
            'phone'        => '89000000000',
            'email'        => 'test@example.test',
        ]);

        $application = Application::create([
            'user_id'      => $this->user->id,
            'client_id'    => $client->id,
            'template_id'  => ApplicationTemplate::where('slug', 'application-legal')->firstOrFail()->id,
            'client_type'  => 'legal',
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
            UploadedFile::fake()->create('org.sig', 2),
            User::factory()->create(['role' => UserRole::Staff]),
        );

        return $this->contracts->publish($contract->fresh());
    }

    public function test_protocol_contains_document_and_client_data(): void
    {
        $contract = $this->awaitingContract();

        $data = $this->protocols->collectData($contract->fresh());

        $this->assertSame($contract->application_id, $data['contract_number']);
        $this->assertSame('dogovor.pdf', $data['file_name']);
        $this->assertSame($contract->file_hash, $data['file_hash']);
        $this->assertStringContainsString('700', $data['signing_reason']);
        $this->assertStringContainsString(SigningReason::PowerThreshold->label(), $data['signing_reason']);
        $this->assertSame('2205001234', $data['client_inn']);
    }

    public function test_protocol_lists_both_signatures(): void
    {
        $contract = $this->awaitingContract();
        $this->ukep->sign($contract, $this->user, UploadedFile::fake()->create('client.sig', 3));

        $data = $this->protocols->collectData($contract->fresh());

        $this->assertCount(2, $data['signatures']);
        $this->assertStringContainsString('Организация', $data['signatures'][0]['signer']);
        $this->assertStringContainsString('Потребитель', $data['signatures'][1]['signer']);
    }

    public function test_protocol_shows_signature_file_hash(): void
    {
        $contract = $this->awaitingContract();

        $data    = $this->protocols->collectData($contract->fresh());
        $details = $data['signatures'][0]['details'];

        $this->assertArrayHasKey('Хеш файла подписи (SHA-256)', $details);
        $this->assertSame(
            $contract->organizationSignature()->first()->signature_file_hash,
            $details['Хеш файла подписи (SHA-256)']
        );
    }

    public function test_protocol_document_is_created_for_client(): void
    {
        $contract = $this->awaitingContract();

        $document = $this->protocols->generate($contract->fresh());

        $this->assertSame(PdfDocumentType::SigningProtocol->value, $document->type);
        $this->assertSame($contract->client_id, $document->client_id);
        $this->assertTrue(Storage::disk('local')->exists($document->file_path));
    }

    public function test_protocol_is_replaced_not_duplicated(): void
    {
        $contract = $this->awaitingContract();

        $first = $this->protocols->generate($contract->fresh());
        sleep(1); // имя файла содержит время — иначе второй перезапишет первый
        $second = $this->protocols->generate($contract->fresh());

        $this->assertSame(1, Document::where('type', PdfDocumentType::SigningProtocol->value)->count());
        $this->assertFalse(Storage::disk('local')->exists($first->file_path));
        $this->assertTrue(Storage::disk('local')->exists($second->file_path));
    }

    public function test_protocol_is_generated_automatically_on_signing(): void
    {
        $contract = $this->awaitingContract();

        $this->ukep->sign($contract, $this->user, UploadedFile::fake()->create('client.sig', 3));

        $this->assertSame(1, Document::where('type', PdfDocumentType::SigningProtocol->value)->count());
    }
}