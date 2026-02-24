<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Inertia\Inertia;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index() {
        $tickets = Ticket::with(['user', 'attachments'])->latest()->get();
        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets
        ]);
    }
}
