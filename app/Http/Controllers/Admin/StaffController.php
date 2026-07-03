<?php

namespace App\Http\Controllers\Admin;

use App\DTO\Staff\CreateStaffDTO;
use App\DTO\Staff\UpdateStaffDTO;
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
        $dto = CreateStaffDTO::fromRequest($request);

        User::create([
            'name'     => $dto->name,
            'email'    => $dto->email,
            'password' => Hash::make($dto->password),
            'role'     => $dto->role->value,
        ]);

        return back()->with('success', 'Сотрудник создан');
    }

    public function update(UpdateStaffRequest $request, User $staff)
    {
        $dto = UpdateStaffDTO::fromRequest($request);

        $updateData = [
            'name'        => $dto->name,
            'email'       => $dto->email,
            'role'        => $dto->role->value,
            'permissions' => $dto->permissions,
        ];

        if ($dto->password) {
            $updateData['password'] = Hash::make($dto->password);
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