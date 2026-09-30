<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Enums\SignatureMethod;
use App\Enums\SignerType;
use App\Mail\ContractSigningCode;
use App\Models\Contract;
use App\Models\ContractPepCode;
use App\Models\ContractSignature;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ContractPepSigningService
{
    private const CODE_TTL_MINUTES        = 10;
    private const MAX_ATTEMPTS            = 5;
    private const RESEND_COOLDOWN_SECONDS = 60;

    public function __construct(
        private ContractService $contracts,
        private ContractProtocolService $protocols,
    ) {}

    /**
     * Выдать клиенту код для подписания договора.
     */
    public function sendCode(Contract $contract, User $user): ContractPepCode
    {
        $this->assertCanSign($contract, $user);

        $email = $contract->client?->email;

        if (! $email) {
            throw ValidationException::withMessages([
                'code' => 'У вас не указан адрес электронной почты. Обратитесь в службу поддержки.',
            ]);
        }

        $this->assertCooldownPassed($contract);

        $code = (string) random_int(100000, 999999);

        $row = DB::transaction(function () use ($contract, $code, $email) {
            // Прежние невостребованные коды больше не годятся:
            // действующий код всегда ровно один.
            $contract->pepCodes()->whereNull('confirmed_at')->delete();

            return ContractPepCode::create([
                'contract_id' => $contract->id,
                'code_hash'   => Hash::make($code),
                'channel'     => 'email',
                'sent_to'     => $email,
                'expires_at'  => now()->addMinutes(self::CODE_TTL_MINUTES),
            ]);
        });

        // Письмо — после транзакции: если почта не отправится, запись о коде
        // останется, и клиент сможет запросить его заново.
        Mail::to($email)->send(new ContractSigningCode(
            $code,
            $contract->client->display_name,
            $contract->application_id,
            self::CODE_TTL_MINUTES,
        ));

        return $row;
    }

    /**
     * Подтвердить код и подписать договор простой электронной подписью.
     */
    public function confirm(
        Contract $contract,
        User $user,
        string $code,
        ?string $ip = null,
        ?string $userAgent = null,
    ): ContractSignature {
        $this->assertCanSign($contract, $user);

        $row = $contract->pepCodes()
            ->whereNull('confirmed_at')
            ->latest('id')
            ->first();

        if (! $row || $row->isExpired()) {
            throw ValidationException::withMessages([
                'code' => 'Код истёк или не запрашивался. Запросите новый.',
            ]);
        }

        if ($row->attempts >= self::MAX_ATTEMPTS) {
            throw ValidationException::withMessages([
                'code' => 'Слишком много неверных попыток. Запросите новый код.',
            ]);
        }

        // Попытку считаем ДО проверки: иначе при сбое на следующих
        // шагах счётчик не увеличится, и перебор станет бесплатным.
        $row->increment('attempts');

        if (! Hash::check($code, $row->code_hash)) {
            throw ValidationException::withMessages([
                'code' => 'Неверный код. Проверьте письмо и попробуйте снова.',
            ]);
        }

        // Файл мог измениться между выдачей кода и вводом.
        // Подписывать можно только то, что клиент видел.
        if (! $contract->fileIsIntact()) {
            throw ValidationException::withMessages([
                'code' => 'Файл договора изменился. Обратитесь в службу поддержки.',
            ]);
        }

        $signature = DB::transaction(function () use ($contract, $row, $ip, $userAgent) {
            $row->update(['confirmed_at' => now()]);

            $signature = ContractSignature::create([
                'contract_id'   => $contract->id,
                'signer'        => SignerType::Client->value,
                'method'        => SignatureMethod::Pep->value,
                'signed_at'     => now(),
                'document_hash' => $contract->file_hash,
                'pep_channel'   => $row->channel,
                'pep_sent_to'   => $row->sent_to,
                'ip'            => $ip,
                'user_agent'    => $userAgent,
            ]);

            $contract->update([
                'status'    => ContractStatus::Signed->value,
                'signed_at' => now(),
            ]);

            $this->contracts->activate($contract->fresh());

            return $signature;
        });

        try {
            $this->protocols->generate($contract->fresh());
        } catch (\Throwable $e) {
            // Протокол — производный документ, его можно создать заново.
            // Подпись уже сохранена, ронять операцию из-за PDF нельзя.
            Log::error('Не удалось сформировать протокол подписания: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
            ]);
        }

        return $signature;
    }

    /**
     * Общие условия: это договор этого клиента, он ждёт подписи,
     * подписывается простой подписью и ещё не подписан.
     */
    private function assertCanSign(Contract $contract, User $user): void
    {
        if ($contract->client?->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'code' => 'Это не ваш договор.',
            ]);
        }

        if ($contract->status !== ContractStatus::AwaitingClient->value) {
            throw ValidationException::withMessages([
                'code' => 'Этот договор не ожидает вашей подписи.',
            ]);
        }

        if ($contract->signature_method !== SignatureMethod::Pep->value) {
            throw ValidationException::withMessages([
                'code' => 'Этот договор подписывается электронной подписью, а не кодом.',
            ]);
        }

        if ($contract->clientSignature()->exists()) {
            throw ValidationException::withMessages([
                'code' => 'Договор уже подписан.',
            ]);
        }
    }

    private function assertCooldownPassed(Contract $contract): void
    {
        $last = $contract->pepCodes()->latest('id')->first();

        if ($last && $last->created_at->diffInSeconds(now()) < self::RESEND_COOLDOWN_SECONDS) {
            throw ValidationException::withMessages([
                'code' => 'Код уже отправлен. Следующий можно запросить через минуту.',
            ]);
        }
    }
}