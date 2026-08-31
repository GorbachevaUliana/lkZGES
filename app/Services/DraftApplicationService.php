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
            return $existing;
        }

        return Application::create([
            'user_id' => $user->id,
            'template_id' => $template->id,
            'client_type' => $template->client_type,
            'data' => [],
            'status' => ApplicationStatus::Draft->value,
        ]);
    }
}