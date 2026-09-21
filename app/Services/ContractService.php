<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Enums\ApplicationStatus;
use App\Enums\SignatureMethod;
use App\Enums\SignerType;
use App\Models\Application;
use App\Models\Contract;
use App\Models\Document;
use App\Models\ContractSignature;
use App\Models\User;
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
                $this->deleteDraftOrganizationSignature($existing);
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

        if ($contract->signing_required) {
            $orgSignature = $contract->organizationSignature()->first();

            if (! $orgSignature) {
                throw ValidationException::withMessages([
                    'contract' => 'Договор не подписан со стороны организации. Загрузите файл подписи.',
                ]);
            }

            // Подпись должна относиться именно к текущему файлу
            if (! hash_equals($orgSignature->document_hash, $contract->file_hash)) {
                throw ValidationException::withMessages([
                    'contract' => 'Подпись организации относится к другой версии файла. Подпишите договор заново.',
                ]);
            }
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

    /**
     * Прикрепить подпись организации к договору.
     *
     * Оператор подписывает PDF токеном у себя на компьютере (КриптоАРМ),
     * получает файл открепленной подписи .sig и загружает его сюда.
     * Криптографически подпись здесь не проверяется — это отдельная задача
     * на будущее. Мы фиксируем, какой файл был подписан (по хешу), кем
     * загружена подпись и когда.
     */
    public function attachOrganizationSignature(
        Contract $contract,
        UploadedFile $signatureFile,
        User $operator,
        ?string $ip = null,
        ?string $userAgent = null,
    ): ContractSignature {
        if ($contract->status !== ContractStatus::Draft->value) {
            throw ValidationException::withMessages([
                'signature' => 'Договор уже направлен потребителю, подпись изменить нельзя.',
            ]);
        }

        if (! $contract->signing_required) {
            throw ValidationException::withMessages([
                'signature' => 'Для этого договора подписание не требуется.',
            ]);
        }

        if (! $contract->fileIsIntact()) {
            throw ValidationException::withMessages([
                'signature' => 'Файл договора не совпадает с загруженным. Загрузите договор заново.',
            ]);
        }

        return DB::transaction(function () use ($contract, $signatureFile, $operator, $ip, $userAgent) {
            $this->deleteDraftOrganizationSignature($contract);

            $path = $signatureFile->store('contract_signatures', 'local');

            return ContractSignature::create([
                'contract_id'         => $contract->id,
                'signer'              => SignerType::Organization->value,
                // Организация всегда подписывает УКЭП. Не путать с
                // contracts.signature_method — там способ подписи КЛИЕНТА.
                'method'              => SignatureMethod::Ukep->value,
                'signed_at'           => now(),
                'document_hash'       => $contract->file_hash,
                'signed_by_user_id'   => $operator->id,
                'signature_file_path' => $path,
                'ip'                  => $ip,
                'user_agent'          => $userAgent,
            ]);
        });
    }

    /**
     * Удалить подпись организации с черновика — вместе с файлом.
     *
     * Допустимо только для черновика: такой документ ещё не покидал
     * организацию, и подпись на нём не имеет внешнего значения.
     * После публикации подписи не удаляются никогда.
     */
    private function deleteDraftOrganizationSignature(Contract $contract): void
    {
        $existing = $contract->organizationSignature()->first();

        if (! $existing) {
            return;
        }

        $oldPath = $existing->signature_file_path;
        $existing->delete();

        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }
    }
}