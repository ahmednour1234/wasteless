<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'active'     => (bool) $this->active,
            'category'   => $this->category->name ?? null,
            'approve'    => (bool) $this->approve,
            'logo'       => $this->logo ? asset($this->logo) : null,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
