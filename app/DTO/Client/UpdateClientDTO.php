<?php

namespace App\DTO\Client;

use App\Enums\ClientType;
use App\Http\Requests\Admin\UpdateClientRequest;

readonly class UpdateClientDTO
{
    public function __construct(
        public ClientType $clientType,
        public ?string    $lastName,
        public ?string    $firstName,
        public ?string    $middleName,
        public ?string    $companyName,
        public ?string    $phone,
        public ?string    $email,
        public ?string    $inn,
        public ?string    $kpp,
        public ?string    $ogrn,
    ) {}

    public static function fromRequest(UpdateClientRequest $request): self
    {
        return new self(
            clientType:  ClientType::from($request->validated('client_type')),
            lastName:    $request->validated('last_name'),
            firstName:   $request->validated('first_name'),
            middleName:  $request->validated('middle_name'),
            companyName: $request->validated('company_name'),
            phone:       $request->validated('phone'),
            email:       $request->validated('email'),
            inn:         $request->validated('inn'),
            kpp:         $request->validated('kpp'),
            ogrn:        $request->validated('ogrn'),
        );
    }
}