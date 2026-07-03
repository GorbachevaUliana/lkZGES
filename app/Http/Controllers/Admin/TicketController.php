<?php

namespace App\Http\Controllers\Admin;

use App\DTO\Ticket\UpdateTicketDTO;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\UpdateTicketRequest;
use Inertia\Inertia;    

class TicketController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Ticket::with([
            'user.client.documents',
            'staff',
            'attachments',
            'repliedBy',
        ]);

        if ($user->role !== UserRole::Admin) {
            $query->where('staff_id', $user->id);
        }

        $tickets = $query->latest()->paginate(50);

        $tickets->getCollection()->transform(function ($ticket) {
            $ticket->attachments->map(function ($attachment) {
                $attachment->url = route('attachments.serve', $attachment->id);
                return $attachment;
            });
            return $ticket;
        });

        return Inertia::render('Admin/Tickets/TicketsIndex', [
            'tickets' => $tickets,
            'staff_members' => User::whereIn('role', [UserRole::Staff->value, UserRole::Admin->value])
                ->where(function ($query) {
                    $query->where('role', UserRole::Admin->value)
                        ->orWhereJsonContains('permissions', 'tickets');
                })
                ->get(['id', 'name']),
        ]);
    }

    public function update(UpdateTicketRequest $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $dto    = UpdateTicketDTO::fromRequest($request);

        $ticket->update([
            'status'      => $dto->status,
            'staff_id'    => $dto->staffId,
            'admin_reply' => $dto->adminReply,
            'replied_at'  => $dto->adminReply ? now() : $ticket->replied_at,
            'replied_by'  => $dto->adminReply ? auth()->id() : $ticket->replied_by,
        ]);

        foreach ($dto->adminFiles as $file) {
            $path = $file->store('tickets/replies', 'local');
            $ticket->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'is_admin'  => true,
            ]);
        }

        return back();
    }
}