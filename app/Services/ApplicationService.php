<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\PropertyStatus;
use App\Models\Application;
use App\Models\Property;
use App\Models\Tariff;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    /**
     * Допустимые переходы между статусами заявки (state machine).
     *
     * Ключ — текущий статус, значение — статусы, в которые можно перейти.
     * Без этого сотрудник мог перевести заявку в любой статус в любом порядке,
     * например повторно approved с другим account_number — что перезаписало бы
     * лицевой счёт уже активного объекта (бизнес-логическая уязвимость).
     */
    private const ALLOWED_TRANSITIONS = [
        ApplicationStatus::New->value => [ApplicationStatus::Pending->value],
        ApplicationStatus::Pending->value => [ApplicationStatus::Processing->value, ApplicationStatus::Rejected->value],
        ApplicationStatus::Processing->value => [ApplicationStatus::Approved->value, ApplicationStatus::Rejected->value],
        ApplicationStatus::Approved->value => [],
        ApplicationStatus::Rejected->value => [],
    ];

    /**
     * Проверить, допустим ли переход из $from в $to.
     */
    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::ALLOWED_TRANSITIONS[$from] ?? [], true);
    }

    /**
     * Обновление статуса заявки с проверкой допустимости перехода.
     *
     * ВАЖНО: Тариф сохраняется на уровне объекта (property), а не клиента!
     * Каждый объект энергоснабжения имеет свой тариф.
     */
    public function updateStatus(Application $application, array $data): void
    {
        $newStatus = $data['status'];
        $currentStatus = $application->status;

        // Проверяем допустимость перехода до начала транзакции,
        // чтобы не занимать блокировку БД при ошибке валидации.
        if (! $this->canTransition($currentStatus, $newStatus)) {
            $currentLabel = Application::getStatuses()[$currentStatus] ?? $currentStatus;
            $newLabel     = Application::getStatuses()[$newStatus]     ?? $newStatus;

            throw ValidationException::withMessages([
                'status' => "Нельзя перевести заявку из статуса «{$currentLabel}» в «{$newLabel}». "
                    . 'Проверьте допустимую последовательность статусов.',
            ]);
        }

        DB::transaction(function () use ($application, $data, $newStatus, $currentStatus) {
            if ($newStatus === ApplicationStatus::Approved->value && $application->client) {
                // Дополнительная защита: если объект уже активен — запрещаем
                // менять его лицевой счёт повторным approved (даже если переход
                // формально допустим по state machine).
                $property = $application->property_id
                    ? Property::find($application->property_id)
                    : $application->client->properties()->latest()->first();

                if ($property && $property->status === PropertyStatus::Active->value && $property->account_number) {
                    throw ValidationException::withMessages([
                        'account_number' => 'Объект уже активен с лицевым счётом '
                            . $property->account_number
                            . '. Повторное одобрение невозможно.',
                    ]);
                }

                $tariff = Tariff::find($data['tariff_id']);

                if ($property) {
                    $property->update([
                        'status' => PropertyStatus::Active->value,
                        'account_number' => $data['account_number'],
                        'tariff_id' => $tariff?->id,
                    ]);
                }

                if ($application->client->user) {
                    $application->client->user->forceFill(['role' => 'client'])->save();
                }
            }

            $application->update([
                'status' => $newStatus,
                'tariff_id' => $data['tariff_id']     ?? $application->tariff_id,
                'admin_comment' => $data['admin_comment'] ?? null,
                'processed_at' => $currentStatus !== $newStatus ? now() : $application->processed_at,
                'processed_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Вернуть список допустимых следующих статусов для текущего.
     * Используется на фронтенде чтобы показывать только доступные варианты.
     */
    public function getAllowedNextStatuses(string $currentStatus): array
    {
        $allowed = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];
        $labels  = Application::getStatuses();

        return array_map(fn ($s) => [
            'value' => $s,
            'label' => $labels[$s] ?? $s,
        ], $allowed);
    }
}