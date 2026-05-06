<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Facades\DB;

class ApplicationService
{
    /**
     * Логика обновления статуса заявки
     */
// App\Services\ApplicationService.php

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
                    // 'tariff_id' => $data['tariff_id'],
                    'tariff_category' => $tariff ? $tariff->name : null, // Сохраняем имя тарифа
                ]);
                
                $application->client->activate($data['account_number']);
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