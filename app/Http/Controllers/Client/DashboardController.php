<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Application;
use App\Models\Document;
use App\Models\MeterReading;
use App\Models\Contract;
use App\Models\Client;
use App\Services\DraftApplicationService;
use App\Enums\ApplicationStatus;
use App\Enums\ContractStatus;
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

        $client = $user->client;

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
            'contract' => $this->contractSummary($client),
        ]);
    }

    /**
     * Страница документов клиента
     */
    public function documents(DraftApplicationService $draftService)
    {
        $user   = auth()->user();
        $client = $user->client;
        $draft  = $draftService->currentForUser($user);
        $contractSummary = $this->contractSummary($client);

        // Больше НЕ редиректим при отсутствии клиента: пользователь с одним
        // лишь черновиком должен видеть раздел; плашку отрисует фронт по draft.
        $documents = $client
            ? Document::where('client_id', $client->id)
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
                })
            : collect();

        // Поданная заявка (НЕ черновик): фильтруем статус, иначе latest()
        // подхватил бы черновик как «последнюю заявку».
        $application = Application::where('user_id', $user->id)
            ->where('status', '!=', ApplicationStatus::Draft->value)
            ->latest()
            ->first();

        if ($contractSummary && $contractSummary['needs_signing']) {
            $documents = $documents
                ->reject(fn ($doc) => $doc['type'] === 'contract'
                    && ($doc['application']['id'] ?? null) === $contractSummary['number'])
                ->values();
        }

        return Inertia::render('Client/Documents', [
            'documents'   => $documents,
            'application' => $application,
            'draft'       => $draft,
            'contract'    => $contractSummary,
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
     * 
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

        $propertyIds = $activeProperties->pluck('id');

        // Было: $totalDebt = 0; — объявлялось и никогда не пересчитывалось,
        // клиент всегда видел долг «0» независимо от реальных неоплаченных
        // показаний. Теперь считаем по-настоящему: сумма total_sum по всем
        // неоплаченным показаниям на активных объектах клиента.
        $totalDebt = MeterReading::whereIn('property_id', $propertyIds)
            ->where('is_paid', false)
            ->sum('total_sum');

        // Было: foreach по объектам с отдельным запросом на каждой
        // итерации (N+1) в поисках первого объекта с показанием. Теперь —
        // один запрос по всем объектам сразу.
        $lastReading = MeterReading::whereIn('property_id', $propertyIds)
            ->latest('reading_date')
            ->value('current_value');

        $tariff = $activeProperties->first()?->tariff?->name;

        return [
            'totalDebt' => $totalDebt,
            'lastReading' => $lastReading,
            'tariff' => $tariff,
        ];
    }

    /**
     * Сводка по договору для личного кабинета.
     *
     * Нужна и на главной, и в документах, поэтому живёт отдельно.
     * Черновики и направленные без подписания сюда не попадают:
     * первые клиент не видит, вторые подписывать не нужно.
     */
    private function contractSummary(?Client $client): ?array
    {
        if (! $client) {
            return null;
        }

        $contract = Contract::where('client_id', $client->id)
            ->whereIn('status', [
                ContractStatus::AwaitingClient->value,
                ContractStatus::Signed->value,
                ContractStatus::Active->value,
            ])
            ->latest('id')
            ->first();

        if (! $contract) {
            return null;
        }

        return [
            'id'            => $contract->id,
            'number'        => $contract->application_id,
            'status'        => $contract->status,
            'status_label'  => $contract->statusLabel(),
            'needs_signing' => $contract->status === ContractStatus::AwaitingClient->value,
            'method'        => $contract->signature_method,
            'signed_at'     => $contract->signed_at?->timezone('Asia/Barnaul')->format('d.m.Y H:i'),
            'url'           => route('client.contracts.download', $contract->id),
            'file_name'     => $contract->original_name,
        ];
    }
}