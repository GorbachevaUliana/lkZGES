<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::whereIn('role', [UserRole::Admin->value, UserRole::Staff->value])->get();

        return Inertia::render('Admin/Staff/StaffList', [
            'staff' => $staff,
        ]);
    }

    // public function store(Request $request)
    public function store(StoreStaffRequest $request)
    {
        // Создаём через forceFill, который намеренно обходит $fillable —
        // здесь это безопасно, потому что данные уже прошли валидацию.
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

    public function update(UpdateStaffRequest $request, User $staff)
    {
        $updateData = [
            'name' => $request->validated()['name'],
            'email' => $request->validated()['email'],
            'role' => $request->validated()['role'],
            'permissions' => $request->validated()['permissions'] ?? [],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->validated()['password']);
        }

        $staff->forceFill($updateData)->save();

        return back();
    }

    public function destroy(User $staff)
    {
        // Операторы (не-админы) могут удалять других операторов,
        // но не администраторов — иначе доступ к разделу "Сотрудники"
        // фактически давал возможность удалить самого администратора.
        if ($staff->role === UserRole::Admin && auth()->user()->role !== UserRole::Admin) {
            abort(403, 'Только администратор может удалить учётную запись администратора.');
        }

        $staff->delete();

        return back();
    }
}