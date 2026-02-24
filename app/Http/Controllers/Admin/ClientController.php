<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function index() {
        $clients = Client::with('documents')->get();
        return Inertia::render('Admin/ClientsList', [
            'clients' => $clients
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_number' => 'required|unique:clients',
            'last_name' => 'required|string',
            'middle_name' => 'required|string',
            'first_name' => 'required|string',
            'address' => 'required',
            'phone' => 'nullable',
            'email' => 'nullable|email',
        ]);

        Client::create($validated);

        return back();
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $validated = $request->validate([
            'last_name' => 'required|string',
            'middle_name' => 'required|string',
            'first_name' => 'required|string',
            'address' => 'required',
            'phone' => 'nullable',
            'email' => 'nullable|email',
        ]);

        $client->update($validated);
        return back();
    }
    public function upload(Request $request, $id) {
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
            Storage::disk('public')->delete($document->file_path);
            $document->delete();
        }

        $client->delete();

        return redirect()->route('admin.clients')
            ->with('message', 'Потребитель успешно удален');
    }
}