<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Facades\DB;

class ApplicationService
{
    public function updateStatus(Application $application, array $data): void
    {
        DB::transaction(function () use ($application, $data) {
            $oldStatus = $application->status;
            $newStatus = $data['status'];

            if ($newStatus === 'approved' && $application->client) {
                $tariff = \App\Models\Tariff::find($data['tariff_id']);

                $application->client->update([
                    'tariff_category' => $tariff ? $tariff->name : null,
                ]);
                
                $propertyId = $application->property_id;
                $propertyActivated = false;
                
                if ($propertyId) {
                    $property = \App\Models\Property::find($propertyId);
                    if ($property) {
                        $property->update([
                            'status' => 'active',
                            'account_number' => $data['account_number']
                        ]);
                        $propertyActivated = true;
                    }
                }
                
                if (!$propertyActivated) {
                    $property = $application->client->properties()->latest()->first();
                    if ($property) {
                        $property->update([
                            'status' => 'active',
                            'account_number' => $data['account_number']
                        ]);
                    }
                }
                
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