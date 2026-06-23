<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::whereIn('role', ['admin', 'staff'])->get();

        return Inertia::render('Admin/Staff/StaffList', [
            'staff' => $staff,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,staff',
            'permissions' => 'nullable|array',
        ]);

        // Назначать роль "администратор" может только сам администратор.
        // Без этой проверки оператор со включённым доступом к разделу
        // "Сотрудники" мог создать себе (или кому угодно) полноценный admin-аккаунт.
        if ($request->role === 'admin' && auth()->user()->role !== 'admin') {
            abort(403, 'Только администратор может назначать роль администратора.');
        }

        // role/status/permissions убраны из User::$fillable (защита от mass-assignment).
        // Создаём через forceFill, который намеренно обходит $fillable —
        // здесь это безопасно, потому что данные уже прошли валидацию выше.
        $user = new User();
        $user->forceFill([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'status'      => 'active',
            'permissions' => $request->permissions ?? [],
        ])->save();

        return back();
    }

    public function update(Request $request, User $staff)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Только администратор может редактировать сотрудников');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$staff->id,
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
        // Операторы (не-админы) могут удалять других операторов,
        // но не администраторов — иначе доступ к разделу "Сотрудники"
        // фактически давал возможность удалить самого администратора.
        if ($staff->role === 'admin' && auth()->user()->role !== 'admin') {
            abort(403, 'Только администратор может удалить учётную запись администратора.');
        }

        $staff->delete();

        return back();
    }
}