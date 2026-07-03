<?php

namespace App\Http\Controllers\Client;

use App\DTO\Ticket\StoreTicketDTO;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use App\Http\Requests\Client\StoreTicketRequest;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function storeTicket(StoreTicketRequest $request)
    {
        $dto    = StoreTicketDTO::fromRequest($request);
        $user   = auth()->user();

        $ticket = $user->tickets()->create([
            'subject' => $dto->subject,
            'message' => $dto->message,
            'status'  => 'new',
        ]);

        foreach ($dto->files as $file) {
            $path = $file->store('tickets', 'local');
            $ticket->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'is_admin'  => false,
            ]);
        }

        return back()->with('success', 'Обращение отправлено');
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
