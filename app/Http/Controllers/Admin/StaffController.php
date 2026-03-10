<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::whereIn('role', ['admin', 'staff'])->get();
        
        return Inertia::render('Admin/Staff/StaffList', [
            'staff' => $staff
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required',
            'permissions' => 'nullable|array',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
        ]);

        return back();
    }

    public function update(Request $request, User $staff)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Только администратор может редактировать сотрудников');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->id,
            'role' => 'required|in:admin,staff',
            'permissions' => 'nullable|array',
            'password' => 'nullable|confirmed|min:8', 
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $staff->update($updateData);
        
        return back();
    }
    public function destroy(User $staff)
        {
            $staff->delete();
            return back();
        }
}