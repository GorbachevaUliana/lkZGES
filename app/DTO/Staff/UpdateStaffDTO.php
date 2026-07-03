<?php

namespace App\DTO\Staff;

use App\Enums\UserRole;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;

readonly class UpdateStaffDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password,
        public UserRole $role,
        public array $permissions,
    ) {}

    public static function fromRequest(UpdateStaffRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            role: UserRole::from($request->validated('role')),
            permissions: $request->validated('permissions', []),
        );
    }
}