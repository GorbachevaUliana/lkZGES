<?php

namespace App\Services;

use App\Enums\ClientType;
use App\Enums\SignatureMethod;
use App\Enums\SigningReason;
use App\Models\Application;

class ContractSigningModeService
{
    /**
     * Определить режим подписания договора по заявке.
     *
     * Возвращает готовый набор полей для создания Contract:
     * нужна ли подпись, на каком основании, каким способом
     * и от какой мощности мы отталкивались.
     *
     * @return array{
     *     signing_required: bool,
     *     signing_reason: string,
     *     signature_method: string,
     *     max_power_kw: float|null
     * }
     */
    public function resolve(Application $application): array
    {
        $power     = $application->max_power_kw;
        // Порядок важен: мощность проверяется ДО желания клиента.
        // При мощности не ниже порога отказаться от подписания нельзя,
        // что бы ни было выбрано в заявке.
        if ($this->isSigningMandatoryByPower($power)) {
            $required = true;
            $reason   = SigningReason::PowerThreshold;
        } elseif ($application->signing_requested === true) {
            $required = true;
            $reason   = SigningReason::ClientRequest;
        } else {
            $required = false;
            $reason   = SigningReason::NotRequired;
        }

        return [
            'signing_required' => $required,
            'signing_reason'   => $reason->value,
            'signature_method' => $this->resolveMethod($application),
            'max_power_kw'     => $power,
        ];
    }

    /**
     * Способ подписи определяется типом лица, а не ролью в сделке.
     * Ветвление живёт в ClientType::signatureMethod() — там match без default,
     * который упадёт при появлении нового типа лица. Это намеренно.
     */
    private function resolveMethod(Application $application): string
    {
        return ClientType::from($application->client_type)
            ->signatureMethod()
            ->value;
    }

    /**
     * Обязательно ли подписание по одной только мощности.
     * Единственное место в проекте, где сравнивается с порогом.
     */
    public function isSigningMandatoryByPower(?float $power): bool
    {
        return $power !== null
            && $power >= (float) config('contracts.signing_power_threshold_kw');
    }
}