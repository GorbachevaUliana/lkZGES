<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Resources\ApplicationResource;

class DashboardController extends Controller
{
    /**
     * Главная страница ЛК
     * ИСПРАВЛЕНО: Правильная загрузка properties для мульти-собственности
     */
    public function index()
    {
        $user = Auth::user();
        $client = $user->client;

        // Получаем только АКТИВНЫЕ объекты с ЛС для карточек "Мои объекты"
        $activeProperties = $user->properties()
            ->where('status', 'active')
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->get();

        // Получаем объекты БЕЗ ЛС (на рассмотрении)
        $pendingProperties = $user->properties()
            ->where(function ($query) {
                $query->where('status', '!=', 'active')
                    ->orWhereNull('account_number')
                    ->orWhere('account_number', '');
            })
            ->get();

        // Получаем активные заявки
        $activeApplications = $user->applications()
            ->whereIn('status', ['pending', 'processing'])
            ->with('property')
            ->latest()
            ->get();

        // Получаем первый активный ЛС для отображения в шапке
        $primaryAccountNumber = $activeProperties->first()?->account_number;

        return Inertia::render('Client/Dashboard', [
            'properties' => $activeProperties,
            'pendingProperties' => $pendingProperties,
            'activeApplications' => $activeApplications,
            'client' => $client,
            'hasActiveProperties' => $activeProperties->count() > 0,
            'primaryAccountNumber' => $primaryAccountNumber,
        ]);
    }

    /**
     * Профиль клиента
     * ИСПРАВЛЕНО: Добавлены hasActiveProperties и properties для навигации
     */
    public function profile()
    {
        $user = Auth::user();
        $client = $user->client;
        $application = Application::where('user_id', $user->id)->latest()->first();

        // ИСПРАВЛЕНО: Получаем активные объекты для навигации
        $activeProperties = $user->properties()
            ->where('status', 'active')
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->get();

        return Inertia::render('Client/Profile', [
            'auth' => ['user' => $user],
            'user' => $user,
            'client' => $client,
            'application' => $application,
            'properties' => $activeProperties,
            'hasActiveProperties' => $activeProperties->count() > 0,
        ]);
    }

    /**
     * Документы клиента
     * ИСПРАВЛЕНО: Добавлены hasActiveProperties и properties для навигации
     */
    public function documents()
    {
        $user = Auth::user();
        $client = $user->client;
        $application = Application::where('user_id', $user->id)->latest()->first();

        // ИСПРАВЛЕНО: Получаем активные объекты для навигации
        $activeProperties = $user->properties()
            ->where('status', 'active')
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->get();

        $documents = [];
        if ($client) {
            $documents = $client->documents()->latest()->get()->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'file_path' => $doc->file_path,
                    'url' => asset('storage/'.$doc->file_path),
                    'type' => $doc->type,
                    'type_name' => $doc->type_name,
                    'created_at' => $doc->created_at->format('d.m.Y'),
                ];
            });
        } elseif ($application && $application->client) {
            $documents = $application->client->documents()->latest()->get()->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'file_path' => $doc->file_path,
                    'url' => asset('storage/'.$doc->file_path),
                    'type' => $doc->type,
                    'type_name' => $doc->type_name,
                    'created_at' => $doc->created_at->format('d.m.Y'),
                ];
            });
        }

        return Inertia::render('Client/Documents', [
            'auth' => ['user' => $user],
            'documents' => $documents,
            'application' => $application,
            'properties' => $activeProperties,
            'hasActiveProperties' => $activeProperties->count() > 0,
        ]);
    }

    /**
     * Заявки пользователя
     */
    public function application()
    {
        $user = auth()->user();
        $activeProperties = $user->properties()
            ->where('status', 'active')
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->get();

        return Inertia::render('Dashboard', [
            'myApplications' => Application::with('template')
                ->where('user_id', auth()->id())
                ->latest()
                ->get(),
            'properties' => $activeProperties,
            'hasActiveProperties' => $activeProperties->count() > 0,
        ]);
    }
}