<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function storeTicket(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120', // до 5МБ
        ]);

        $ticket = Ticket::create([
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('tickets_attachments', 'local');
                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return back();
    }

    public function ticketsIndex()
    {
        $tickets = auth()->user()->tickets()
            ->with(['attachments'])
            ->latest()
            ->get()
            ->map(function ($ticket) {
                $ticket->attachments->map(function ($attachment) {
                    $attachment->url = route('attachments.serve', $attachment->id);

                    return $attachment;
                });

                return $ticket;
            });

        return Inertia::render('Client/Tickets', [
            'tickets' => $tickets,
        ]);
    }
}
