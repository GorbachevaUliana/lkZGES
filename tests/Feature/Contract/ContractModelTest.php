<?php

namespace Tests\Feature\Contract;

use App\Enums\ApplicationStatus;
use App\Enums\ContractStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\Client;
use App\Models\Contract;
use App\Models\User;
use Database\Seeders\ApplicationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ApplicationTemplateSeeder::class);

        // Подменяла диск на виртуальный, чтобы тесты не трогали реальные файлы,
        // и чтобы после каждого теста всё стиралось само
        Storage::fake('local');
    }

    private function makeContract(string $content = 'Содержимое договора'): Contract
    {
        $user = User::factory()->create(['role' => UserRole::Applicant]);

        $client = Client::create([
            'user_id'     => $user->id,
            'client_type' => 'individual',
            'last_name'   => 'Иванов',
            'first_name'  => 'Иван',
            'phone'       => '89000000000',
        ]);

        $template = ApplicationTemplate::where('slug', 'application-individual')->firstOrFail();

        $application = Application::create([
            'user_id'     => $user->id,
            'client_id'   => $client->id,
            'template_id' => $template->id,
            'client_type' => 'individual',
            'data'        => [],
            'status'      => ApplicationStatus::Pending->value,
        ]);

        $path = 'contracts/test-contract.pdf';
        Storage::disk('local')->put($path, $content);

        return Contract::create([
            'application_id' => $application->id,
            'client_id'      => $client->id,
            'client_type'    => 'individual',
            'file_path'      => $path,
            'original_name'  => 'dogovor.pdf',
            'file_hash'      => hash('sha256', $content),
            'status'         => ContractStatus::Draft->value,
        ]);
    }

    public function test_contract_is_linked_to_application_and_client(): void
    {
        $contract = $this->makeContract();

        $this->assertTrue($contract->application->contract->is($contract));
        $this->assertTrue($contract->client->contracts->contains($contract));
    }

    public function test_file_is_intact_when_file_was_not_changed(): void
    {
        $contract = $this->makeContract();

        $this->assertTrue($contract->fileIsIntact());
    }

    public function test_file_is_not_intact_after_file_was_replaced(): void
    {
        $contract = $this->makeContract();

        Storage::disk('local')->put($contract->file_path, 'Подменённый файл');

        $this->assertFalse($contract->fileIsIntact());
    }
}