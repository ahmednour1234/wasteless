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
use App\Models\Transaction;
use App\Http\Resources\OrderResource;
use App\Services\PaymentService;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
        $validator = Validator::make($request->all(), [
            'items'                 => 'required|array|min:1',
            'items.*.bundle_id'     => 'required|exists:bundles,id',
            'items.*.quantity'      => 'required|integer|min:1',
            'address'               => 'nullable|string',
            'name'                  => 'nullable|string',
            'phone'                 => 'nullable|string',
            'payment_type'          => 'nullable|in:whish_money,omt_pay,bank',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = Customer::findOrFail(Auth::id());
        $paymentType = $request->input('payment_type', 'whish_money');

        $totalSub      = 0;
        $totalDiscount = 0;
        $orderItems    = [];

        try {
            foreach ($request->items as $item) {
                $bundle = Bundle::where('id', $item['bundle_id'])
                    ->where('active', 1)
                    ->first();

                if (!$bundle) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Bundle not found or inactive',
                    ], 404);
                }

                $originalPrice = $bundle->price;
                $finalPrice    = $bundle->price_after_discount ?? $originalPrice;
                $discount      = $originalPrice - $finalPrice;
                $quantity      = $item['quantity'];
                $total         = $originalPrice * $quantity;

                $totalSub      += $total;
                $totalDiscount += $discount * $quantity;

                $orderItems[] = [
                    'bundle'    => $bundle->toArray(),
                    'quantity'  => $quantity,
                    'price'     => $originalPrice,
                    'discount'  => $discount,
                    'total'     => $total,
                    'snapshot'  => $bundle->toArray(),
                ];
            }

            $orderName  = $request->input('name')  ?: $customer->name;
            $orderPhone = $request->input('phone') ?: $customer->phone;
            $totalAmount = $totalSub - $totalDiscount;

            $appUrl = config('app.url');
            $baseUrl = (strpos($appUrl, 'http://') === 0 ? str_replace('http://', 'https://', $appUrl) : $appUrl) . '/api';
            
            do {
                $externalId = (string) (time() . rand(10000, 99999));
            } while (Transaction::where('external_id', $externalId)->exists());

            $transaction = Transaction::create([
                'external_id' => $externalId,
                'payment_type' => $paymentType,
                'amount' => $totalAmount,
                'currency' => 'USD',
                'status' => Transaction::STATUS_PENDING,
                'invoice' => 'Order Payment - ' . $orderName,
                'success_callback_url' => $baseUrl . '/user/payments/callback/success',
                'failure_callback_url' => $baseUrl . '/user/payments/callback/failure',
                'success_redirect_url' => $request->input('success_redirect_url', $baseUrl . '/user/payments/callback/success'),
                'failure_redirect_url' => $request->input('failure_redirect_url', $baseUrl . '/user/payments/callback/failure'),
                'metadata' => [
                    'customer_id' => $customer->id,
                    'items' => $orderItems,
                    'address' => $request->input('address', ''),
                    'name' => $orderName,
                    'phone' => $orderPhone,
                    'sub_total' => $totalSub,
                    'total_discount' => $totalDiscount,
                ],
            ]);

            $paymentService = new PaymentService();
            $paymentResult = $paymentService->initiatePayment($transaction, [
                'items' => $orderItems,
                'customer' => $customer,
            ]);

            if (!$paymentResult['success']) {
                $transaction->update(['status' => Transaction::STATUS_FAILED]);
                return response()->json([
                    'status'  => false,
                    'message' => $paymentResult['message'] ?? 'Payment initiation failed',
                ], 400);
            }

            return response()->json([
                'status'         => true,
                'message'        => 'Payment initiated successfully',
                'transaction_id' => $transaction->id,
                'payment_type'   => $paymentType,
                'collect_url'    => $paymentResult['collect_url'],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
