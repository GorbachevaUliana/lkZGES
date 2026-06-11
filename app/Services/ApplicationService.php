<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Facades\DB;

class ApplicationService
{
    /**
     * Обновление статуса заявки
     *
     * ВАЖНО: Тариф сохраняется на уровне объекта (property), а не клиента!
     * Каждый объект энергоснабжения имеет свой тариф.
     */
    public function updateStatus(Application $application, array $data): void
    {
        DB::transaction(function () use ($application, $data) {
            $oldStatus = $application->status;
            $newStatus = $data['status'];

            if ($newStatus === 'approved' && $application->client) {
                $tariff = \App\Models\Tariff::find($data['tariff_id']);

                // Определяем объект для активации
                $propertyId = $application->property_id;
                $property = null;

                if ($propertyId) {
                    $property = \App\Models\Property::find($propertyId);
                }

                if (!$property) {
                    $property = $application->client->properties()->latest()->first();
                }

                // Активируем объект с лицевым счётом и тарифом
                if ($property) {
                    $property->update([
                        'status' => 'active',
                        'account_number' => $data['account_number'],
                        'tariff_id' => $tariff ? $tariff->id : null, // Тариф привязан к объекту!
                    ]);
                }

                // Обновляем роль пользователя
                if ($application->client->user) {
                    $application->client->user->update(['role' => 'client']);
                }
            }

            $application->update([
                'status' => $newStatus,
                'tariff_id' => $data['tariff_id'] ?? $application->tariff_id,
                'admin_comment' => $data['admin_comment'] ?? null,
                'processed_at' => $oldStatus !== $newStatus ? now() : $application->processed_at,
                'processed_by' => auth()->id(),
            ]);
        });
    }
}
