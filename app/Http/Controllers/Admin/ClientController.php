<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Property;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::with(['documents', 'properties.tariff'])
            ->whereHas('properties', function ($query) {
                $query->where('status', 'active')
                    ->whereNotNull('account_number')
                    ->where('account_number', '!=', '');
            })
            ->get()
            ->map(function ($client) {
                $activeProperties = $client->properties->filter(function ($property) {
                    return $property->status === 'active'
                        && !empty($property->account_number);
                })->values();

                $statusName = $client->status_name;
                $status = $activeProperties->count() > 0 ? 'active' : 'inactive';

                // $propertyAddress = $activeProperties->first()?->address ?? $client->address;
                $propertyAddress = $activeProperties->first()?->address ?? null;
                $accountNumbersStr = $activeProperties->pluck('account_number')->implode(', ');

                // Получаем тарифы из активных объектов
                // $tariffs = $activeProperties->map(function ($property) {
                //     return $property->tariff ? [
                //         'id' => $property->tariff->id,
                //         'name' => $property->tariff->name,
                //     ] : null;
                // })->filter()->values();
                $propertiesData = $activeProperties->map(function($property) {
                    return [
                        'id' => $property->id,
                        'account_number' => $property->account_number,
                        'address' => $property->address,
                        'status' => $property->status,
                        'tariff' => $property->tariff ? [
                            'id' => $property->tariff->id,
                            'name' => $property->tariff->name,
                            'price_1' => $property->tariff->price_1, 
                        ] : null,
                    ];
                })->values();

                return [
                    'id' => $client->id,
                    'user_id' => $client->user_id,
                    'client_type' => $client->client_type,
                    'client_type_name' => $client->client_type_name,
                    'last_name' => $client->last_name,
                    'first_name' => $client->first_name,
                    'middle_name' => $client->middle_name,
                    'full_name' => $client->full_name,
                    'display_name' => $client->display_name,
                    'company_name' => $client->company_name,
                    'inn' => $client->inn,
                    'address' => $propertyAddress,
                    'phone' => $client->phone,
                    'email' => $client->email,
                    'status' => $status,
                    'status_name' => $statusName,
                    'documents' => $client->documents,
                    // 'properties' => $activeProperties,
                    'properties' => $propertiesData,
                    'properties_count' => $activeProperties->count(),
                    // 'tariffs' => $tariffs,
                    'account_number' => $accountNumbersStr ?: null,
                    'account_numbers' => $accountNumbersStr,
                    'created_at' => $client->created_at,
                ];
            });

        return Inertia::render('Admin/ClientsList', [
            'clients' => $clients,
            'tariffs' => Tariff::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_type' => 'required|in:individual,legal',
            'last_name' => 'nullable|string',
            'first_name' => 'nullable|string',
            'middle_name' => 'nullable|string',
            'company_name' => 'nullable|string',
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'properties' => 'required|array|min:1',
            'properties.*.account_number' => 'required|string|distinct',
            'properties.*.tariff_id' => 'required|exists:tariffs,id',
            'properties.*.region' => 'nullable|string',
            'properties.*.district' => 'nullable|string',
            'properties.*.locality' => 'required|string',
            'properties.*.street' => 'required|string',
            'properties.*.house' => 'required|string',
            'properties.*.building' => 'nullable|string',
            'properties.*.apartment' => 'nullable|string',
        ]);

        // Проверка уникальности лицевых счетов в БД
        foreach ($validated['properties'] as $prop) {
            if (Property::where('account_number', $prop['account_number'])->exists()) {
                return back()->withErrors([
                    'properties' => "Лицевой счет {$prop['account_number']} уже существует в базе данных."
                ]);
            }
        }

        // Создаём клиента
        $client = Client::create([
            'client_type' => $validated['client_type'],
            'last_name' => $validated['last_name'] ?? null,
            'first_name' => $validated['first_name'] ?? null,
            'middle_name' => $validated['middle_name'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
        ]);

        // Создаём объекты недвижимости
        foreach ($validated['properties'] as $propData) {
            // Сборка полного адреса
            $addressParts = [];
            $addressParts[] = $propData['region'] ?? 'Алтайский край';
            if (!empty($propData['district'])) {
                $addressParts[] = $propData['district'] . ' район';
            }
            $addressParts[] = $propData['locality'];
            $addressParts[] = 'ул. ' . $propData['street'];
            $addressParts[] = 'д. ' . $propData['house'];
            if (!empty($propData['building'])) {
                $addressParts[] = 'корп. ' . $propData['building'];
            }
            if (!empty($propData['apartment'])) {
                $addressParts[] = 'кв. ' . $propData['apartment'];
            }
            $fullAddress = implode(', ', $addressParts);

            Property::create([
                'client_id' => $client->id,
                'tariff_id' => $propData['tariff_id'],
                'account_number' => $propData['account_number'],
                'address' => $fullAddress,
                'status' => 'active',
            ]);
        }

        return back()->with('success', 'Потребитель успешно создан');
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'client_type' => 'required|in:individual,legal',
            'last_name' => 'nullable|string',
            'first_name' => 'nullable|string',
            'middle_name' => 'nullable|string',
            'company_name' => 'nullable|string',
            'phone' => 'nullable',
            'email' => 'nullable|email',
        ]);

        $client->update($validated);

        return back()->with('success', 'Данные обновлены');
    }

    public function upload(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,jpg,png|max:10240',
        ]);

        $client = Client::findOrFail($id);
        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        $client->documents()->create([
            'name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'type' => $file->getClientOriginalExtension(),
        ]);

        return back()->with('success', 'Файл загружен');
    }

    public function destroy(Client $client)
    {
        foreach ($client->documents as $document) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
        }
        $client->documents()->delete();
        $client->delete();

        return back();
    }
}