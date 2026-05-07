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
    // /**
    //  * Главная страница ЛК
    //  */
    // public function index()
    // {
    //     $user = Auth::user();
    //     $client = $user->client;
    //     $application = Application::where('user_id', $user->id)
    //         ->latest()
    //         ->first();

    //     return Inertia::render('Client/Dashboard', [
    //         // 'application' => $application ? new ApplicationResource($application) : null,
    //         'properties' => $user->client->properties()->where('status', 'active')->get(),
    //         'activeApplications' => $user->applications()->whereIn('status', ['pending', 'processing'])->with('property')->get(),
    //         'client' => $user->client,
    //     ]);
    // }
    // //     $applicationData = null;
    // //     if ($application) {
    // //         $applicationData = [
    // //             'id' => $application->id,
    // //             'status' => $application->status,
    // //             'created_at' => $application->created_at->format('d.m.Y H:i'),
    // //             'admin_comment' => $application->admin_comment,
    // //             'client' => $application->client ? [
    // //                 'id' => $application->client->id,
    // //                 'account_number' => $application->client->account_number,
    // //                 'first_name' => $application->client->first_name,
    // //                 'last_name' => $application->client->last_name,
    // //                 'middle_name' => $application->client->middle_name,
    // //             ] : null,
    // //         ];
    // //     }

    // //     if ($user->role === 'applicant') {
    // //         return Inertia::render('Client/Dashboard', [
    // //             'auth' => ['user' => $user],
    // //             'client' => $client,
    // //             'application' => $applicationData,
    // //             'stats' => null,
    // //         ]);
    // //     }

    // //     return Inertia::render('Client/Dashboard', [
    // //         'auth' => ['user' => $user],
    // //         'client' => $client ? [
    // //             'id' => $client->id,
    // //             'account_number' => $client->account_number,
    // //             'first_name' => $client->first_name,
    // //             'last_name' => $client->last_name,
    // //             'middle_name' => $client->middle_name,
    // //             'phone' => $client->phone,
    // //             'address' => $client->address,
    // //         ] : null,
    // //         'application' => $applicationData,
    // //         'stats' => [
    // //             'clients_count' => \App\Models\Client::count(),
    // //             'tickets_count' => Ticket::where('status', 'new')->count(),
    // //         ],
    // //     ]);
    // // }

    // /**
    //  * Профиль клиента
    //  */
    // public function profile()
    // {
    //     $user = Auth::user();
    //     $application = Application::where('user_id', $user->id)->latest()->first();

    //     return Inertia::render('Client/Profile', [
    //         'auth' => ['user' => $user],
    //         'user' => $user,
    //         'client' => $user->client,
    //         'application' => $application,
    //     ]);
    // }

    // public function documents()
    // {
    //     $user = Auth::user();
    //     $client = $user->client;
    //     $application = Application::where('user_id', $user->id)->latest()->first();

    //     $documents = [];
    //     if ($client) {
    //         $documents = $client->documents()->latest()->get()->map(function ($doc) {
    //             return [
    //                 'id' => $doc->id,
    //                 'name' => $doc->name,
    //                 'file_path' => $doc->file_path,
    //                 'url' => asset('storage/'.$doc->file_path),
    //                 'type' => $doc->type,
    //                 'type_name' => $doc->type_name,
    //                 'created_at' => $doc->created_at->format('d.m.Y'),
    //             ];
    //         });
    //     } elseif ($application && $application->client) {
    //         $documents = $application->client->documents()->latest()->get()->map(function ($doc) {
    //             return [
    //                 'id' => $doc->id,
    //                 'name' => $doc->name,
    //                 'file_path' => $doc->file_path,
    //                 'url' => asset('storage/'.$doc->file_path),
    //                 'type' => $doc->type,
    //                 'type_name' => $doc->type_name,
    //                 'created_at' => $doc->created_at->format('d.m.Y'),
    //             ];
    //         });
    //     }

    //     return Inertia::render('Client/Documents', [
    //         'auth' => ['user' => $user],
    //         'documents' => $documents,
    //         'application' => $application,
    //     ]);
    // }

    // /**
    //  * Заявки пользователя
    //  */
    // public function application()
    // {
    //     return Inertia::render('Dashboard', [
    //         'myApplications' => Application::with('template')
    //             ->where('user_id', auth()->id())
    //             ->latest()
    //             ->get(),
    //     ]);
    // }
    /**
     * Главная страница ЛК
     * ИСПРАВЛЕНО: Правильная загрузка properties для мульти-собственности
     */
    public function index()
    {
        $user = Auth::user();
        $client = $user->client;

        // Получаем ВСЕ активные объекты пользователя (не только с active status)
        // для отображения в списке "Мои объекты"
        $properties = $user->properties()->get();

        // Получаем активные заявки
        $activeApplications = $user->applications()
            ->whereIn('status', ['pending', 'processing'])
            ->with('property')
            ->latest()
            ->get();

        return Inertia::render('Client/Dashboard', [
            'properties' => $properties,
            'activeApplications' => $activeApplications,
            'client' => $client,
            'hasActiveProperties' => $user->hasActiveProperties(),
        ]);
    }
    //     $applicationData = null;
    //     if ($application) {
    //         $applicationData = [
    //             'id' => $application->id,
    //             'status' => $application->status,
    //             'created_at' => $application->created_at->format('d.m.Y H:i'),
    //             'admin_comment' => $application->admin_comment,
    //             'client' => $application->client ? [
    //                 'id' => $application->client->id,
    //                 'account_number' => $application->client->account_number,
    //                 'first_name' => $application->client->first_name,
    //                 'last_name' => $application->client->last_name,
    //                 'middle_name' => $application->client->middle_name,
    //             ] : null,
    //         ];
    //     }

    //     if ($user->role === 'applicant') {
    //         return Inertia::render('Client/Dashboard', [
    //             'auth' => ['user' => $user],
    //             'client' => $client,
    //             'application' => $applicationData,
    //             'stats' => null,
    //         ]);
    //     }

    //     return Inertia::render('Client/Dashboard', [
    //         'auth' => ['user' => $user],
    //         'client' => $client ? [
    //             'id' => $client->id,
    //             'account_number' => $client->account_number,
    //             'first_name' => $client->first_name,
    //             'last_name' => $client->last_name,
    //             'middle_name' => $client->middle_name,
    //             'phone' => $client->phone,
    //             'address' => $client->address,
    //         ] : null,
    //         'application' => $applicationData,
    //         'stats' => [
    //             'clients_count' => \App\Models\Client::count(),
    //             'tickets_count' => Ticket::where('status', 'new')->count(),
    //         ],
    //     ]);
    // }

    /**
     * Профиль клиента
     */
    public function profile()
    {
        $user = Auth::user();
        $application = Application::where('user_id', $user->id)->latest()->first();

        return Inertia::render('Client/Profile', [
            'auth' => ['user' => $user],
            'user' => $user,
            'client' => $user->client,
            'application' => $application,
        ]);
    }

    public function documents()
    {
        $user = Auth::user();
        $client = $user->client;
        $application = Application::where('user_id', $user->id)->latest()->first();

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
        ]);
    }

    /**
     * Заявки пользователя
     */
    public function application()
    {
        return Inertia::render('Dashboard', [
            'myApplications' => Application::with('template')
                ->where('user_id', auth()->id())
                ->latest()
                ->get(),
        ]);
    }
}