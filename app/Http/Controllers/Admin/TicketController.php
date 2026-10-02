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
    public function index(Request $request)
    {
        $user   = auth()->user();
        $search = trim((string) $request->input('search'));
        $like   = Ticket::query()->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query = Ticket::with([
            'user.client.documents',
            'staff',
            'attachments',
            'repliedBy',
        ]);

        if ($user->role !== UserRole::Admin) {
            $query->where('staff_id', $user->id);
        }

        $query->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search, $like) {
            $inner->where('subject', $like, "%{$search}%")
                ->orWhere('message', $like, "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', $like, "%{$search}%")
                    ->orWhere('email', $like, "%{$search}%"))
                ->orWhereHas('user.client.properties', fn ($p) => $p->where('account_number', $like, "%{$search}%"));
        }));

        $tickets = $query->latest()->paginate(50)->withQueryString();

        $tickets->getCollection()->transform(function ($ticket) {
                $ticket->attachments->map(function ($attachment) {
                    $attachment->url = route('attachments.serve', $attachment->id);
                    return $attachment;
                });
                return $ticket;
            });

        return Inertia::render('Admin/Tickets/TicketsIndex', [
            'tickets'       => $tickets,
            'search'        => $search,
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