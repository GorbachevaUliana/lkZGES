<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Facades\DB;

class ApplicationService
{
    /**
     * Логика обновления статуса заявки
     * При одобрении активируется конкретное свойство из заявки (property_id)
     */
    public function updateStatus(Application $application, array $data): void
    {
        DB::transaction(function () use ($application, $data) {
            $oldStatus = $application->status;
            $newStatus = $data['status'];

            if ($newStatus === 'approved' && $application->client) {
                // 1. Находим данные тарифа по ID
                $tariff = \App\Models\Tariff::find($data['tariff_id']);

                // 2. Обновляем клиента, добавляя tariff_category
                $application->client->update([
                    'tariff_category' => $tariff ? $tariff->name : null,
                ]);
                
                // 3. Активируем конкретное свойство из заявки
                $propertyId = $application->property_id;
                $propertyActivated = false;
                
                if ($propertyId) {
                    // Активируем свойство по ID из заявки
                    $property = \App\Models\Property::find($propertyId);
                    if ($property) {
                        $property->update([
                            'status' => 'active',
                            'account_number' => $data['account_number']
                        ]);
                        $propertyActivated = true;
                    }
                }
                
                // Fallback: если property_id нет или свойство не найдено
                if (!$propertyActivated) {
                    // Находим последнее свойство клиента
                    $property = $application->client->properties()->latest()->first();
                    if ($property) {
                        $property->update([
                            'status' => 'active',
                            'account_number' => $data['account_number']
                        ]);
                    }
                }
                
                // 4. Обновляем роль пользователя на 'client' (только один раз!)
                if ($application->client->user) {
                    $application->client->user->update(['role' => 'client']);
                }
            }

            // Обновляем саму заявку
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