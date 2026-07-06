<?php

namespace App\Http\Resources;

use App\Enums\PropertyStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $activeProperties = $this->whenLoaded('properties', function () {
            return $this->properties->filter(
                fn($p) => $p->status === PropertyStatus::Active->value
                    && !empty($p->account_number)
            )->values();
        }, collect());

        return [
            'id'               => $this->id,
            'user_id'          => $this->user_id,
            'client_type'      => $this->client_type,
            'client_type_name' => $this->client_type_name,
            'last_name'        => $this->last_name,
            'first_name'       => $this->first_name,
            'middle_name'      => $this->middle_name,
            'full_name'        => $this->full_name,
            'display_name'     => $this->display_name,
            'company_name'     => $this->company_name,
            'inn'              => $this->inn,
            'kpp'              => $this->kpp,
            'ogrn'             => $this->ogrn,
            'phone'            => $this->phone,
            'email'            => $this->email,
            'address'          => $activeProperties->first()?->address,
            'account_number'   => $activeProperties->pluck('account_number')->implode(', ') ?: null,
            'account_numbers'  => $activeProperties->pluck('account_number')->implode(', '),
            'status'           => $activeProperties->isNotEmpty() ? 'active' : 'inactive',
            'status_name'      => $this->status_name,
            'properties_count' => $activeProperties->count(),
            'properties'       => PropertyResource::collection(
                $this->whenLoaded('properties', fn() => $activeProperties)
            ),
            'documents'        => DocumentResource::collection($this->whenLoaded('documents')),
            'created_at'       => $this->created_at,
        ];
    }
}