<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
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

        if ($user->role !== 'admin') {
            $query->where('staff_id', $user->id);
        }

        $tickets = $query->latest()->get()->map(function ($ticket) {
            $ticket->attachments->map(function ($attachment) {
                $attachment->url = asset('storage/'.$attachment->file_path);

                return $attachment;
            });

            return $ticket;
        });

        return Inertia::render('Admin/Tickets/TicketsIndex', [
            'tickets' => $tickets,
            'staff_members' => User::whereIn('role', ['staff', 'admin'])
                ->where(function ($query) {
                    $query->where('role', 'admin')
                        ->orWhereJsonContains('permissions', 'tickets');
                })
                ->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'status' => 'required|string',
            'staff_id' => 'nullable|exists:users,id',
            'admin_reply' => 'nullable|string',
            'admin_files.*' => 'nullable|file|max:10240',
        ]);

        $ticket->update([
            'status' => $request->status,
            'staff_id' => $request->staff_id,
            'admin_reply' => $request->admin_reply,
            'replied_at' => $request->admin_reply ? now() : $ticket->replied_at,
            'replied_by' => $request->admin_reply ? auth()->id() : $ticket->replied_by,
        ]);

        if ($request->hasFile('admin_files')) {
            foreach ($request->file('admin_files') as $file) {
                $path = $file->store('tickets/replies', 'public');
                $ticket->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'is_admin' => true,
                ]);
            }
        }

        return back();
    }
}