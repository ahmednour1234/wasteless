<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'status'        => $this->status,
            'total'         => $this->sub_total + $this->delivery - $this->total_discount,
            'sub_total'     => $this->sub_total,
            'total_discount'=> $this->total_discount,
            'delivery'      => $this->delivery,
            'address'       => $this->address,
            'phone'         => $this->phone,
            'name'          => $this->name,
            'created_at'    => $this->created_at,
            'details'       => OrderDetailResource::collection($this->whenLoaded('details')),
        ];
    }
}
