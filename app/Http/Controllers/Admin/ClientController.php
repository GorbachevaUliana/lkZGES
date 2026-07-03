<?php

namespace App\Http\Controllers\Admin;

use App\DTO\Client\CreateClientDTO;
use App\DTO\Client\UpdateClientDTO;
use App\Enums\PropertyStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Property;
use App\Models\Tariff;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Http\Requests\Admin\UploadClientFileRequest;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        $clientsPaginator = Client::with(['documents', 'properties.tariff'])
            ->whereHas('properties', function ($query) {
                $query->where('status', 'active')
                    ->whereNotNull('account_number')
                    ->where('account_number', '!=', '');
            })
            ->paginate(50);

        $clientsPaginator->getCollection()->transform(function ($client) {
            $activeProperties = $client->properties->filter(function ($property) {
                return $property->status === 'active' && !empty($property->account_number);
            })->values();

            $propertiesData = $activeProperties->map(function ($property) {
                return [
                    'id'             => $property->id,
                    'account_number' => $property->account_number,
                    'address'        => $property->address,
                    'status'         => $property->status,
                    'tariff'         => $property->tariff ? [
                        'id'      => $property->tariff->id,
                        'name'    => $property->tariff->name,
                        'price_1' => $property->tariff->price_1,
                    ] : null,
                ];
            })->values();

            return [
                'id'               => $client->id,
                'user_id'          => $client->user_id,
                'client_type'      => $client->client_type,
                'client_type_name' => $client->client_type_name,
                'last_name'        => $client->last_name,
                'first_name'       => $client->first_name,
                'middle_name'      => $client->middle_name,
                'full_name'        => $client->full_name,
                'display_name'     => $client->display_name,
                'company_name'     => $client->company_name,
                'inn'              => $client->inn,
                'address'          => $activeProperties->first()?->address ?? null,
                'phone'            => $client->phone,
                'email'            => $client->email,
                'status'           => $activeProperties->count() > 0 ? 'active' : 'inactive',
                'status_name'      => $client->status_name,
                'documents'        => $client->documents,
                'properties'       => $propertiesData,
                'properties_count' => $activeProperties->count(),
                'account_number'   => $activeProperties->pluck('account_number')->implode(', ') ?: null,
                'account_numbers'  => $activeProperties->pluck('account_number')->implode(', '),
                'created_at'       => $client->created_at,
            ];
        });

        return Inertia::render('Admin/ClientsList', [
            'clients' => $clientsPaginator,
            'tariffs' => Tariff::all(),
        ]);
    }

    public function store(StoreClientRequest $request)
    {
        $dto = CreateClientDTO::fromRequest($request);

        $client = Client::create([
            'client_type'  => $dto->clientType->value,
            'last_name'    => $dto->lastName,
            'first_name'   => $dto->firstName,
            'middle_name'  => $dto->middleName,
            'company_name' => $dto->companyName,
            'phone'        => $dto->phone,
            'email'        => $dto->email,
            'inn'          => $dto->inn,
            'kpp'          => $dto->kpp,
            'ogrn'         => $dto->ogrn,
        ]);

        foreach ($dto->properties as $propData) {
            $addressParts = array_filter([
                $propData['region'] ?? 'Алтайский край',
                !empty($propData['district']) ? $propData['district'] . ' район' : null,
                $propData['locality'],
                'ул. ' . $propData['street'],
                'д. ' . $propData['house'],
                !empty($propData['building']) ? 'корп. ' . $propData['building'] : null,
                !empty($propData['apartment']) ? 'кв. ' . $propData['apartment'] : null,
            ]);

            Property::create([
                'client_id' => $client->id,
                'tariff_id' => $propData['tariff_id'],
                'account_number' => $propData['account_number'],
                'address' => implode(', ', $addressParts),
                'status' => PropertyStatus::Active->value,
            ]);
        }

        return back()->with('success', 'Потребитель успешно создан');
    }

    public function update(UpdateClientRequest $request, $id)
    {
        $client = Client::findOrFail($id);
        $dto = UpdateClientDTO::fromRequest($request);

        $client->update([
            'client_type'  => $dto->clientType->value,
            'last_name'    => $dto->lastName,
            'first_name'   => $dto->firstName,
            'middle_name'  => $dto->middleName,
            'company_name' => $dto->companyName,
            'phone'        => $dto->phone,
            'email'        => $dto->email,
            'inn'          => $dto->inn,
            'kpp'          => $dto->kpp,
            'ogrn'         => $dto->ogrn,
        ]);

        return back()->with('success', 'Данные обновлены');
    }

    public function upload(UploadClientFileRequest $request, $id)
    {
        $client = Client::findOrFail($id);
        $file = $request->file('file');
        $path = $file->store('documents', 'local');

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
            if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {
                Storage::disk('local')->delete($document->file_path);
            }
        }
        $client->documents()->delete();
        $client->delete();

        return back();
    }
}