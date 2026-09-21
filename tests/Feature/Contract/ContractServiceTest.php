<?php

namespace Tests\Feature\Contract;

use App\Enums\ApplicationStatus;
use App\Enums\ContractStatus;
use App\Enums\SignatureMethod;
use App\Enums\SigningReason;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use App\Services\ContractService;
use Database\Seeders\ApplicationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ContractServiceTest extends TestCase
{
    use RefreshDatabase;

    private ContractService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationTemplateSeeder::class);
        Storage::fake('local');
        config(['contracts.signing_power_threshold_kw' => 670]);

        $this->service = app(ContractService::class);
    }

    private function makeApplication(
        ?float $power = null,
        ?bool $requested = null,
        string $clientType = 'individual',
        string $status = ApplicationStatus::Approved->value
    ): Application {
        $user = User::factory()->create(['role' => UserRole::Applicant]);

        $client = Client::create([
            'user_id'     => $user->id,
            'client_type' => $clientType,
            'last_name'   => 'Иванов',
            'first_name'  => 'Иван',
            'phone'       => '89000000000',
        ]);

        $slug = $clientType === 'legal' ? 'application-legal' : 'application-individual';

        return Application::create([
            'user_id'           => $user->id,
            'client_id'         => $client->id,
            'template_id'       => ApplicationTemplate::where('slug', $slug)->firstOrFail()->id,
            'client_type'       => $clientType,
            'data'              => [],
            'status'            => $status,
            'max_power_kw'      => $power,
            'signing_requested' => $requested,
        ]);
    }

    private function pdf(string $name = 'dogovor.pdf'): UploadedFile
    {
        return UploadedFile::fake()->create($name, 50, 'application/pdf');
    }

    // ==================== СОЗДАНИЕ ====================

    public function test_upload_creates_contract_in_draft(): void
    {
        $contract = $this->service->createFromUpload($this->makeApplication(), $this->pdf());

        $this->assertSame(ContractStatus::Draft->value, $contract->status);
        $this->assertSame('dogovor.pdf', $contract->original_name);
        $this->assertTrue(Storage::disk('local')->exists($contract->file_path));
        $this->assertTrue($contract->fileIsIntact());
    }

    public function test_draft_contract_is_not_visible_to_client(): void
    {
        $this->service->createFromUpload($this->makeApplication(), $this->pdf());

        $this->assertSame(0, Document::where('type', 'contract')->count());
    }

    public function test_signing_mode_is_taken_from_application(): void
    {
        $contract = $this->service->createFromUpload($this->makeApplication(1500.0), $this->pdf());

        $this->assertTrue($contract->signing_required);
        $this->assertSame(SigningReason::PowerThreshold->value, $contract->signing_reason);
        $this->assertSame(1500.0, $contract->max_power_kw);
    }

    public function test_legal_client_gets_ukep_method(): void
    {
        $application = $this->makeApplication(15.0, true, 'legal');
        $contract    = $this->service->createFromUpload($application, $this->pdf());

        $this->assertSame(SignatureMethod::Ukep->value, $contract->signature_method);
    }

    // ==================== ПЕРЕЗАГРУЗКА ====================

    public function test_draft_file_can_be_replaced(): void
    {
        $application = $this->makeApplication();

        $first  = $this->service->createFromUpload($application, $this->pdf('first.pdf'));
        $oldPath = $first->file_path;

        $second = $this->service->createFromUpload($application->fresh(), $this->pdf('second.pdf'));

        $this->assertSame($first->id, $second->id, 'Договор должен обновиться, а не создаться заново');
        $this->assertSame('second.pdf', $second->original_name);
        $this->assertFalse(Storage::disk('local')->exists($oldPath), 'Старый файл должен удаляться');
    }

    public function test_published_contract_cannot_be_replaced(): void
    {
        $application = $this->makeApplication();
        $contract    = $this->service->createFromUpload($application, $this->pdf());

        $this->service->publish($contract);

        $this->expectException(ValidationException::class);
        $this->service->createFromUpload($application->fresh(), $this->pdf('other.pdf'));
    }

    // ==================== ПУБЛИКАЦИЯ ====================

    public function test_publish_without_signing_goes_to_sent(): void
    {
        $contract = $this->service->createFromUpload($this->makeApplication(15.0), $this->pdf());

        $published = $this->service->publish($contract);

        $this->assertSame(ContractStatus::Sent->value, $published->status);
    }

    public function test_publish_with_signing_goes_to_awaiting_client(): void
    {
        $contract = $this->service->createFromUpload($this->makeApplication(700.0), $this->pdf());

        $published = $this->service->publish($contract);

        $this->assertSame(ContractStatus::AwaitingClient->value, $published->status);
    }

    public function test_publish_makes_contract_visible_to_client(): void
    {
        $contract = $this->service->createFromUpload($this->makeApplication(), $this->pdf());

        $this->service->publish($contract);

        $document = Document::where('type', 'contract')->first();

        $this->assertNotNull($document);
        $this->assertSame($contract->client_id, $document->client_id);
        $this->assertSame($contract->file_path, $document->file_path);
    }

    public function test_publish_is_rejected_when_file_was_tampered(): void
    {
        $contract = $this->service->createFromUpload($this->makeApplication(), $this->pdf());

        Storage::disk('local')->put($contract->file_path, 'подменённое содержимое');

        $this->expectException(ValidationException::class);
        $this->service->publish($contract);
    }

    public function test_contract_cannot_be_published_twice(): void
    {
        $contract = $this->service->createFromUpload($this->makeApplication(), $this->pdf());

        $this->service->publish($contract);

        $this->expectException(ValidationException::class);
        $this->service->publish($contract->fresh());
    }

    public function test_contract_cannot_be_published_before_approval(): void
    {
        $application = $this->makeApplication(
            status: ApplicationStatus::Pending->value
        );
        $contract = $this->service->createFromUpload($application, $this->pdf());

        $this->expectException(ValidationException::class);
        $this->service->publish($contract);
    }
}

