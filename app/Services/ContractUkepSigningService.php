<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Enums\SignatureMethod;
use App\Enums\SignerType;
use App\Models\Contract;
use App\Models\ContractSignature;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContractUkepSigningService
{
    public function __construct(
        private ContractService $contracts,
    ) {}

    /**
     * Подписание договора клиентом — юрлицом или ИП.
     *
     * Клиент подписывает PDF своей УКЭП у себя (КриптоАРМ, Контур.Крипто,
     * Госключ) и загружает файл открепленной подписи. Криптографически
     * подпись пока не проверяется — это отдельная задача; сейчас мы
     * фиксируем, какой файл был подписан, кем и когда.
     */
    public function sign(
        Contract $contract,
        User $user,
        UploadedFile $signatureFile,
        ?string $ip = null,
        ?string $userAgent = null,
    ): ContractSignature {
        if ($contract->client?->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'file' => 'Это не ваш договор.',
            ]);
        }

        if ($contract->status !== ContractStatus::AwaitingClient->value) {
            throw ValidationException::withMessages([
                'file' => 'Этот договор не ожидает вашей подписи.',
            ]);
        }

        if ($contract->signature_method !== SignatureMethod::Ukep->value) {
            throw ValidationException::withMessages([
                'file' => 'Этот договор подписывается кодом подтверждения, а не электронной подписью.',
            ]);
        }

        if ($contract->clientSignature()->exists()) {
            throw ValidationException::withMessages([
                'file' => 'Договор уже подписан.',
            ]);
        }

        // Подписать можно только тот файл, который клиент скачивал.
        if (! $contract->fileIsIntact()) {
            throw ValidationException::withMessages([
                'file' => 'Файл договора изменился. Обратитесь в службу поддержки.',
            ]);
        }

        return DB::transaction(function () use ($contract, $signatureFile, $ip, $userAgent) {
            $path = $signatureFile->store('contract_signatures', 'local');

            $signature = ContractSignature::create([
                'contract_id'         => $contract->id,
                'signer'              => SignerType::Client->value,
                'method'              => SignatureMethod::Ukep->value,
                'signed_at'           => now(),
                'document_hash'       => $contract->file_hash,
                'signature_file_path' => $path,
                'ip'                  => $ip,
                'user_agent'          => $userAgent,
            ]);

            $contract->update([
                'status'    => ContractStatus::Signed->value,
                'signed_at' => now(),
            ]);

            $this->contracts->activate($contract->fresh());

            return $signature;
        });
    }
}