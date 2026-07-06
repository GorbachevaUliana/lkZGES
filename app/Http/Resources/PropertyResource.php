<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'account_number' => $this->account_number,
            'address'        => $this->address,
            'status'         => $this->status,
            'tariff'         => $this->whenLoaded('tariff', fn() => [
                'id'      => $this->tariff->id,
                'name'    => $this->tariff->name,
                'price_1' => $this->tariff->price_1,
            ]),
        ];
    }
}