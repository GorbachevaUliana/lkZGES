<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Document;

class DashboardController extends Controller
{
    // public function index()
    // {
    //     $user = Auth::user();
    //     $clientData = $user->client_id ? $user->client->load('documents') : null;

    //     return Inertia::render('Client/Dashboard', [
    //         'client' => $clientData,
    //         'user' => $user
    //     ]);
    // }
    public function index() {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'clients_count' => \App\Models\Client::count(),
                'tickets_count' => \App\Models\Ticket::where('status', 'new')->count(),
            ]
        ]);
    }

    public function profile()
    {
        return Inertia::render('Client/Profile', [
            'user' => Auth::user(),
            'client' => Auth::user()->client
        ]);
    }

    public function documents()
    {
        $client = Auth::user()->client;
        return Inertia::render('Client/Documents', [
            'documents' => $client ? $client->documents : []
        ]);
        
    }
}