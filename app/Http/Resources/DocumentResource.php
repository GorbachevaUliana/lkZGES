<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'original_name'=> $this->original_name,
            'url'          => route('documents.serve', $this->id),
            'type'         => $this->type,
            'type_name'    => $this->type_name,
            'description'  => $this->description,
            'created_at'   => $this->created_at?->format('d.m.Y'),
        ];
    }
}