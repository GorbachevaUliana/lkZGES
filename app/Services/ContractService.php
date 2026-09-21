<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Contract;
use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ContractService
{
    public function __construct(
        private ContractSigningModeService $signingMode,
    ) {}

    /**
     * Создать договор по заявке из загруженного файла — или заменить файл,
     * если договор ещё в черновике.
     *
     * Договор создаётся в статусе draft: файл лежит на диске, хеш посчитан,
     * режим подписания определён, но клиент его не видит. Видимым договор
     * становится при публикации, когда создаётся запись Document.
     */
    public function createFromUpload(Application $application, UploadedFile $file): Contract
    {
        $existing = $application->contract;

        // Заменять можно только черновик. Направленный или подписанный
        // договор — документ, за которым стоит хеш и, возможно, подпись;
        // подменять под ним файл нельзя.
        if ($existing && $existing->status !== ContractStatus::Draft->value) {
            throw ValidationException::withMessages([
                'file' => 'Договор уже направлен потребителю. Заменить файл нельзя: '
                    . 'потребуется расторжение и новый договор.',
            ]);
        }

        return DB::transaction(function () use ($application, $file, $existing) {
            $path = $file->store('contracts', 'local');
            $hash = hash('sha256', Storage::disk('local')->get($path));

            $attributes = array_merge(
                $this->signingMode->resolve($application),
                [
                    'application_id' => $application->id,
                    'client_id'      => $application->client_id,
                    'client_type'    => $application->client_type,
                    'file_path'      => $path,
                    'original_name'  => $file->getClientOriginalName(),
                    'file_hash'      => $hash,
                    'status'         => ContractStatus::Draft->value,
                ]
            );

            if ($existing) {
                $oldPath = $existing->file_path;
                $existing->update($attributes);
                Storage::disk('local')->delete($oldPath);

                return $existing->fresh();
            }

            return Contract::create($attributes);
        });
    }

    /**
     * Направить договор потребителю.
     *
     * До этого момента договор существует только для оператора: файл лежит
     * на диске, но в личном кабинете его нет. Публикация создаёт Document —
     * именно он делает договор видимым клиенту — и переводит договор
     * в awaiting_client, если нужна подпись, или в sent, если не нужна.
     */
    public function publish(Contract $contract): Contract
    {
        if ($contract->application->status !== ApplicationStatus::Approved->value) {
            throw ValidationException::withMessages([
                'contract' => 'Заявка не одобрена. Сначала одобрите заявку, затем направляйте договор.',
            ]);
        }
        
        if ($contract->status !== ContractStatus::Draft->value) {
            throw ValidationException::withMessages([
                'contract' => 'Договор уже направлен потребителю.',
            ]);
        }

        // Файл мог измениться на диске после загрузки. Направлять потребителю
        // документ, не совпадающий с тем, по которому посчитан хеш, нельзя:
        // на этот хеш потом ляжет подпись.
        if (! $contract->fileIsIntact()) {
            throw ValidationException::withMessages([
                'contract' => 'Файл договора не совпадает с загруженным. Загрузите договор заново.',
            ]);
        }

        return DB::transaction(function () use ($contract) {
            $contract->update([
                'status' => $contract->signing_required
                    ? ContractStatus::AwaitingClient->value
                    : ContractStatus::Sent->value,
            ]);

            Document::create([
                'client_id'      => $contract->client_id,
                'application_id' => $contract->application_id,
                'name'           => 'Договор №' . $contract->application_id,
                'file_path'      => $contract->file_path,
                'type'           => 'contract',
                'description'    => 'Договор энергоснабжения',
            ]);

            return $contract->fresh();
        });
    }
}