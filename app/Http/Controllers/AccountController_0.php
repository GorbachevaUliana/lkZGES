<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->role === 'client' || $user->role === 'applicant') {
            return redirect()->route('client.dashboard');
        }

        return Inertia::render('WelcomePage');
    }

    public function link(Request $request)
    {
        $request->validate([
            'account_number' => 'required',
            'last_name' => 'required',
        ]);
        $client = Client::where('account_number', $request->account_number)
            ->where('last_name', 'ILIKE', $request->last_name)
            ->first();

        if (! $client) {
            return back()->withErrors(['account_number' => 'Клиент с такими данными не найден.']);
        }
        $user = auth()->user();
        $user->update([
            'client_id' => $client->id,
            'role' => 'CLIENT',
        ]);

        return redirect()->route('dashboard');
    }
}
