<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Client;
use App\Models\Property;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConstructorController extends Controller
{
    public function submit(Request $request)
    {
        $validatedData = $request->validate([
            'last_name' => 'required|string',
            'first_name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
        ]);

        return DB::transaction(function () use ($validatedData) {
            $user = auth()->user();

            $client = Client::create([
                'last_name' => $validatedData['last_name'],
                'first_name' => $validatedData['first_name'],
                'middle_name' => $validatedData['middle_name'] ?? null,
                'phone' => $validatedData['phone'],
                'email' => $user->email,
            ]);

            $property = Property::create([
                'client_id' => $client->id,
                'address' => $validatedData['address'],
                'status' => 'pending',
            ]);

            $user->update([
                'role' => 'applicant',
            ]);

            $pdf = Pdf::loadView('pdf.application_contract', [
                'data' => $validatedData,
                'user_email' => $user->email,
                'application_id' => time(),
            ]);

            $fileName = 'app_'.$client->id.'_'.time().'.pdf';
            $filePath = 'applications/'.$fileName;
            Storage::disk('public')->put($filePath, $pdf->output());
            Application::create([
                'user_id' => $user->id,
                'client_id' => $client->id,
                'property_id' => $property->id,
                'template_id' => 1,
                'data' => $validatedData,
                'status' => 'pending',
            ]);

            return redirect()->route('client.dashboard')
                ->with('success', 'Заявка успешно отправлена!');
        });
    }
}
