<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'applicant_name' => $this->applicant_name,

            'client_type' => $this->client_type,
            'client_type_name' => $this->client_type_name,

            'status' => $this->status,
            'status_name' => $this->status_name,

            'created_at' => $this->created_at?->format('d.m.Y H:i'),

            'generated_pdf_url' => $this->generated_pdf_url,
            'contract_pdf_url' => $this->contract_pdf_url,

            'admin_comment' => $this->admin_comment,
            'tariff_id' => $this->tariff_id,

            'data' => $this->data ?? [],

            'client' => new ClientResource($this->whenLoaded('client')),

            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],
        ];
    }
}