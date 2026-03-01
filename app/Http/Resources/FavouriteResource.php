<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavouriteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'bundle'   => new BundleResource($this->whenLoaded('bundle')),
            'created_at' => $this->created_at,
        ];
    }
}
