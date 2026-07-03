<?php

namespace App\DTO\Client;

use App\Enums\ClientType;
use App\Http\Requests\Admin\StoreClientRequest;

readonly class CreateClientDTO
{
    public function __construct(
        public ClientType $clientType,
        public ?string $lastName,
        public ?string $firstName,
        public ?string $middleName,
        public ?string $companyName,
        public ?string $inn,
        public ?string $kpp,
        public ?string $ogrn,
        public ?string $phone,
        public ?string $email,
        public array $properties,
    ) {}

    public static function fromRequest(StoreClientRequest $request): self
    {
        return new self(
            clientType: ClientType::from($request->validated('client_type')),
            lastName: $request->validated('last_name'),
            firstName: $request->validated('first_name'),
            middleName: $request->validated('middle_name'),
            companyName: $request->validated('company_name'),
            inn: $request->validated('inn'),
            kpp: $request->validated('kpp'),
            ogrn: $request->validated('ogrn'),
            phone: $request->validated('phone'),
            email: $request->validated('email'),
            properties: $request->validated('properties', []),
        );
    }
}
