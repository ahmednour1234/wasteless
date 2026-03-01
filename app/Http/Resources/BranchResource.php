<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray($request)
{
    return [
        'id'         => $this->id,
        'name'       => $this->name,
        'address'    => $this->address,
        'phone'      => $this->phone,
        'latitude'   => (float) $this->lat,
        'longitude'  => (float) $this->lng,
        'company_id' => $this->company_id,
        'active'     => (bool)   $this->active,   // ← here
                'main'     => (bool)   $this->main,   // ← here
        'created_at' => $this->created_at->toDateTimeString(),
        'updated_at' => $this->updated_at->toDateTimeString(),
    ];
}

    public function with($request)
    {
        return [
            'status' => 'success',
            'message' => 'Branch data retrieved successfully.',
        ];
    }
}

