<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Bundle;
use App\Models\Customer;
use App\Http\Resources\OrderResource;
use Exception;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    public function index()
    {
        $customer = Customer::where('id', Auth::id())->firstOrFail();

        $orders = Order::with(['details.bundle'])
            ->where('customer_id', $customer->id)
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    public function show($id)
    {
        $customer = Customer::where('id', Auth::id())->firstOrFail();

        $order = Order::with(['details.bundle'])
            ->where('customer_id', $customer->id)
            ->findOrFail($id);

        return new OrderResource($order);
    }
 public function store(Request $request)
    {
        // 1) Validate request
        $validator = Validator::make($request->all(), [
            'items'                 => 'required|array|min:1',
            'items.*.bundle_id'     => 'required|exists:bundles,id',
            'items.*.quantity'      => 'required|integer|min:1',
            'address'               => 'nullable|string',
            'name'                  => 'nullable|string',
            'phone'                 => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // 2) Get authenticated customer
        $customer = Customer::findOrFail(Auth::id());

        $totalSub      = 0;
        $totalDiscount = 0;
        $orderItems    = [];

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                // 3) Load bundle and check availability
                $bundle = Bundle::where('id', $item['bundle_id'])
                    ->where('active', 1)
                    ->first();


                // 4) Pricing calculations
                $originalPrice = $bundle->price;
                $finalPrice    = $bundle->price_after_discount ?? $originalPrice;
                $discount      = $originalPrice - $finalPrice;
                $quantity      = $item['quantity'];
                $total         = $originalPrice * $quantity;

                $totalSub      += $total;
                $totalDiscount += $discount * $quantity;

                // 5) Prepare order items data
                $orderItems[] = [
                    'bundle'    => $bundle,
                    'quantity'  => $quantity,
                    'price'     => $originalPrice,
                    'discount'  => $discount,
                    'total'     => $total,
                    'snapshot'  => $bundle->toArray(),
                ];
            }

            // 6) Determine name and phone
            $orderName  = $request->input('name')  ?: $customer->name;
            $orderPhone = $request->input('phone') ?: $customer->phone;

            // 7) Create the order
            $order = Order::create([
                'customer_id'    => $customer->id,
                'status'         => 'pending',
                'sub_total'      => $totalSub,
                'total_discount' => $totalDiscount,
                'delivery'       => 0,
                'address'        => $request->input('address', ''),
                'name'           => $orderName,
                'phone'          => $orderPhone,
            ]);

            // 8) Save order details and decrement stock
            foreach ($orderItems as $item) {
                OrderDetail::create([
                    'order_id'    => $order->id,
                    'bundle_id'   => $item['bundle']->id,
                    'company_id'  => $item['bundle']->company_id,
                    'branch_id'   => $item['bundle']->branch_id,
                    'category_id' => $item['bundle']->category_id,
                    'quantity'    => $item['quantity'],
                    'price'       => $item['price'],
                    'discount'    => $item['discount'],
                    'total'       => $item['total'],
                    'bundles'     => $item['snapshot'],
                    'status'      => 'pending',
                ]);

                $item['bundle']->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'status'   => true,
                'message'  => 'تم إنشاء الطلب بنجاح',
                'order_id' => $order->id,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
