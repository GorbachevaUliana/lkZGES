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
    /**
     * Список потребителей
     * ИСПРАВЛЕНО: Показываем только клиентов с активными объектами (ЛС)
     * При мульти-собственности один клиент может иметь несколько объектов
     */
    public function index()
    {
        // Фильтруем клиентов, у которых есть хотя бы один активный объект с ЛС
        // Загружаем properties заранее через with()
        $clients = Client::with(['documents', 'tariff', 'readings', 'properties'])
            ->whereHas('properties', function ($query) {
                $query->where('status', 'active')
                    ->whereNotNull('account_number')
                    ->where('account_number', '!=', '');
            })
            ->get()
            ->map(function ($client) {
                // Фильтруем только активные properties из уже загруженных
                $activeProperties = $client->properties->filter(function ($property) {
                    return $property->status === 'active' 
                        && !empty($property->account_number);
                })->values();

                // Определяем статус на основе активных объектов
                $statusName = $client->status_name; // Вычисляется через аксессор
                $status = $activeProperties->count() > 0 ? 'active' : 'inactive';
                
                // Получаем адрес из первого активного объекта
                $propertyAddress = $activeProperties->first()?->address ?? $client->address;
                // Получаем все ЛС одной строкой
                $accountNumbersStr = $activeProperties->pluck('account_number')->implode(', ');
                
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
                    // ИСПРАВЛЕНО: адрес берём из первого активного объекта
                    'address' => $propertyAddress,
                    'phone' => $client->phone,
                    'email' => $client->email,
                    'status' => $status,
                    'status_name' => $statusName,
                    'tariff_id' => $client->tariff_id,
                    'tariff' => $client->tariff,
                    'tariff_category' => $client->tariff_category,
                    'documents' => $client->documents,
                    'readings' => $client->readings,
                    'properties' => $activeProperties,
                    'properties_count' => $activeProperties->count(),
                    // ИСПРАВЛЕНО: Добавляем account_number для совместимости с фронтендом
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
            'account_number' => 'required|unique:clients',
            'client_type' => 'required|in:individual,legal',
            'last_name' => 'nullable|string',
            'first_name' => 'nullable|string',
            'middle_name' => 'nullable|string',
            'company_name' => 'nullable|string',
            'address' => 'required',
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'tariff_id' => 'required|exists:tariffs,id',
        ]);

        Client::create($validated);

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
            'address' => 'required',
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'tariff_id' => 'required|exists:tariffs,id',
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