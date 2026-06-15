<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Application;
use App\Models\Document;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Главная страница личного кабинета
     */

    public function index()
    {
        $user = auth()->user();

        $properties = Property::whereHas('client', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->activeWithAccount()
            ->with(['client.user', 'tariff'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($property) {
                return [
                    'id' => $property->id,
                    'account_number' => $property->account_number,
                    'address' => $property->address ?? $property->client?->address,
                    'property_type' => $property->property_type,
                    'area' => $property->area,
                    'status' => $property->status,
                    'meter_number' => $property->meter_number,
                    'tariff' => $property->tariff,
                    'client' => $property->client ? [
                        'id' => $property->client->id,
                        'address' => $property->client->address,
                    ] : null,
                ];
            });

        $activeApplications = Application::where('user_id', $user->id)
            ->whereIn('status', ['new', 'processing', 'pending'])
            ->with('property')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = $this->getStats($user);

        return Inertia::render('Client/Dashboard', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'properties' => $properties,
            'activeApplications' => $activeApplications,
            'stats' => $stats,
        ]);
    }

    /**
     * Страница документов клиента
     */
    public function documents()
    {
        $user = auth()->user();
        $client = $user->client;
        if (!$client) {
            return redirect()->route('welcome.step');
        }
        $documents = Document::where('client_id', $client->id)
            ->with('application:id,status')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'file_path' => $doc->file_path,
                    'type' => $doc->type,
                    'type_name' => $doc->type_name,
                    'description' => $doc->description,
                    'url' => $doc->url,
                    'created_at' => $doc->created_at->format('d.m.Y H:i'),
                    'application' => $doc->application ? [
                        'id' => $doc->application->id,
                        'status' => $doc->application->status,
                    ] : null,
                ];
            });
        $application = Application::where('user_id', $user->id)
            ->latest()
            ->first();

        return Inertia::render('Client/Documents', [
            'documents' => $documents,
            'application' => $application,
        ]);
    }

    /**
     * Страница списка объектов
     */
    public function properties()
    {
        $user = auth()->user();

        $properties = Property::whereHas('client', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->activeWithAccount()
            ->with(['tariff', 'meterReadings' => function ($query) {
                $query->latest('reading_date')->limit(5);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Client/Properties', [
            'properties' => $properties,
        ]);
    }

    /**
     * Страница профиля
     */
    public function profile()
    {
        $user = auth()->user();
        $client = $user->client;

        return Inertia::render('Client/Profile', [
            'user' => $user,
            'client' => $client?->load('properties'),
        ]);
    }

    /**
     * Получить статистику для дашборда
     */
    private function getStats($user)
    {
        $client = $user->client;

        if (!$client) {
            return [
                'totalDebt' => 0,
                'lastReading' => null,
                'tariff' => null,
            ];
        }

        $activeProperties = $client->properties()
            ->where('status', 'active')
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->with('tariff')
            ->get();

        if ($activeProperties->isEmpty()) {
            return [
                'totalDebt' => 0,
                'lastReading' => null,
                'tariff' => null,
            ];
        }

        $totalDebt = 0;

        $lastReading = null;
        foreach ($activeProperties as $property) {
            $lastMeterReading = $property->meterReadings()->latest('reading_date')->first();
            if ($lastMeterReading) {
                $lastReading = $lastMeterReading->current_value;
                break;
            }
        }

        $tariff = $activeProperties->first()?->tariff?->name;

        return [
            'totalDebt' => $totalDebt,
            'lastReading' => $lastReading,
            'tariff' => $tariff,
        ];
    }
}