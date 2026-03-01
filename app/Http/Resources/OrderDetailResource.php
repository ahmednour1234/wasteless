<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'bundle'   => new BundleResource($this->whenLoaded('bundle')),
            'quantity' => $this->quantity,
            'price'    => $this->price,
            'discount' => $this->discount,
            'total'    => $this->total,
                    'status'   => $this->status, // تمت الإضافة

        ];
    }
}
