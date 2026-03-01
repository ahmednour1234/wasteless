<?php
// app/Http/Resources/CustomerResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use App\Models\OrderDetail;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 1) عدد البندلات عبر order_details
        $bundlesCount = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.customer_id', $this->id)
            ->count();

        // 2) إجمالي المبالغ: SUM(sub_total - total_discount)
        $ordersTotal = DB::table('orders')
            ->where('customer_id', $this->id)
            ->selectRaw('COALESCE(SUM(sub_total - total_discount), 0) as total')
            ->value('total');

        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'img'            => $this->img ? asset($this->img) : null,
            'bundles_count'  => $bundlesCount,
            'orders_total'   => round($ordersTotal, 2),
        ];
    }
}
