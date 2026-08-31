<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\User;

class DraftApplicationService
{
    public function getOrCreateForUser(User $user, ApplicationTemplate $template): Application
    {
        $existing = Application::where('user_id', $user->id)
            ->where('status', ApplicationStatus::Draft->value)
            ->first();

        if ($existing) {
            if ($existing->client_type !== $template->client_type) {
                $existing->update([
                    'client_type' => $template->client_type,
                    'template_id' => $template->id,
                ]);
            }

            return $existing;
        }

        return Application::create([
            'user_id'     => $user->id,
            'template_id' => $template->id,
            'client_type' => $template->client_type,
            'data'        => [],
            'status'      => ApplicationStatus::Draft->value,
        ]);
    }

    public function finalizeForUser(User $user, array $attributes): Application
    {
        $payload = array_merge($attributes, [
            'status'             => ApplicationStatus::Pending->value,
            'generated_pdf_path' => '',
        ]);

        $draft = Application::where('user_id', $user->id)
            ->where('status', ApplicationStatus::Draft->value)
            ->first();

        if ($draft) {
            $draft->update($payload);
            return $draft;
        }

        return Application::create(array_merge($payload, [
            'user_id' => $user->id,
        ]));
    }

        /** Текущий черновик пользователя (или null). Для разделов ЛК. */
    public function currentForUser(User $user): ?Application
    {
        return Application::where('user_id', $user->id)
            ->where('status', ApplicationStatus::Draft->value)
            ->first();
    }
}