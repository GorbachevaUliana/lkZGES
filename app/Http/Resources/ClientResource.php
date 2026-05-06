<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\DocumentResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'address' => $this->address,
            'phone' => $this->phone,
            'account_number' => $this->account_number,
            'company_name' => $this->company_name,
            'inn' => $this->inn,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
