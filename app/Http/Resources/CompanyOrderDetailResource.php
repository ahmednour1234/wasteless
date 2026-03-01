<?php
// app/Http/Resources/CompanyOrderDetailResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyOrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_id'    => $this->order_id,
            'bundle'      => new BundleResource($this->whenLoaded('bundle')),
            'quantity'    => $this->quantity,
            'price'       => $this->price,
            'discount'    => $this->discount,
            'total'       => $this->total,
            'status'      => $this->status,
            'customer'    => [
                'name'  => $this->order->name,
                'phone' => $this->order->phone,
                'address' => $this->order->address,
            ],
        ];
    }
}
