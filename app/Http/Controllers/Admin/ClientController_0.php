<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/ClientsList', [
            'clients' => Client::with(['documents', 'tariff', 'readings'])->get(),
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
