<?php

namespace App\Http\Controllers;

use App\Models\ApplicationTemplate;
use App\Models\Client;
use App\Services\ApplicationSubmitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationSubmitController extends Controller
{
    public function __construct(
        private ApplicationSubmitService $submitService,
    ) {}

    public function show(Request $request, string $slug): Response|RedirectResponse
    {
        $template = ApplicationTemplate::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $user           = auth()->user();
        $existingClient = Client::where('user_id', $user->id)->first();

        // Если у пользователя уже есть профиль другого типа — ведём
        // на форму, соответствующую его реальному типу клиента.
        if ($existingClient && $existingClient->client_type !== $template->client_type) {
            $correctSlug = ApplicationTemplate::where('client_type', $existingClient->client_type)
                ->where('is_active', true)
                ->value('slug');

            if ($correctSlug) {
                return redirect()->route('application.show', $correctSlug)
                    ->with('error', 'У вас уже есть профиль другого типа клиента — открыта соответствующая форма.');
            }
        }

        return Inertia::render('Applications/DynamicForm', [
            'template' => $template->only(['id', 'title', 'slug', 'content', 'client_type']),
        ]);
    }

    public function submit(Request $request, string $slug): RedirectResponse
    {
        $template = ApplicationTemplate::where('slug', $slug)->firstOrFail();

        $user           = auth()->user();
        $existingClient = Client::where('user_id', $user->id)->first();

        // Защита от прямого POST мимо формы — тип клиента должен совпадать
        if ($existingClient && $existingClient->client_type !== $template->client_type) {
            abort(403, 'Тип формы не совпадает с типом вашего профиля.');
        }

        return $this->submitService->handle($request, $template, $user);
    }
}