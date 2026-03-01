<?php
// app/Http/Resources/ReviewResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'bundle_id'   => $this->bundle_id,
            'customer_id' => $this->customer_id,
            'bundle_data' => $this->bundle_data,
            'rating'      => $this->rating,
               'customer' => [
                'id'   => $this->customer_id,
                'name' => $this->customer->name ?? null,
            ],
            'comment'     => $this->comment,
            'created_at'  => $this->created_at,
        ];
    }
}
