<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\ClientResource;
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
        $clients = Client::with(['documents', 'properties.tariff'])
            ->whereHas('properties', fn($q) => $q->where('status', PropertyStatus::Active->value)
                ->whereNotNull('account_number')
                ->where('account_number', '!=', ''))
            ->paginate(50);

        return Inertia::render('Admin/ClientsList', [
            'clients' => ClientResource::collection($clients),
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