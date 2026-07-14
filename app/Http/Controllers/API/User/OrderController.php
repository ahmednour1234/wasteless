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
use App\Services\LoyaltyService;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $customer = Customer::where('id', Auth::id())->firstOrFail();

        $orders = Order::with(['details.bundle', 'transaction'])
            ->where('customer_id', $customer->id)
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    public function show($id)
    {
        $customer = Customer::where('id', Auth::id())->firstOrFail();

        $order = Order::with(['details.bundle', 'transaction'])
            ->where('customer_id', $customer->id)
            ->findOrFail($id);

        return new OrderResource($order);
    }
    /**
     * Cancel a pending reservation owned by the authenticated customer.
     * Only orders whose status is "pending" can be cancelled.
     */
    public function cancel($id)
    {
        $customer = Customer::where('id', Auth::id())->firstOrFail();

        $order = Order::with('details.bundle')
            ->where('customer_id', $customer->id)
            ->find($id);

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Order not found',
            ], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'status'  => false,
                'message' => 'Only pending reservations can be cancelled',
            ], 422);
        }

        try {
            DB::transaction(function () use ($order) {
                // Restore stock for each reserved bundle.
                foreach ($order->details as $detail) {
                    if ($detail->bundle) {
                        $detail->bundle->increment('stock', $detail->quantity);
                    }
                    $detail->update(['status' => 'cancelled']);
                }

                $order->update(['status' => 'cancelled']);

                if ($order->transaction) {
                    $order->transaction->update(['status' => Transaction::STATUS_CANCELLED]);
                }
            });
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Reservation cancelled successfully',
            'data'    => [
                'id'     => $order->id,
                'status' => 'cancelled',
            ],
        ]);
    }

    /**
     * Lightweight active order reminder for the Home screen.
     * Returns the nearest upcoming collection for a pending order, or null.
     */
    public function activeReminder()
    {
        $customer = Customer::where('id', Auth::id())->firstOrFail();

        $order = Order::with(['details.bundle'])
            ->where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->first(function ($order) {
                $bundle = optional($order->details->first())->bundle;
                return $bundle && $bundle->opening_time
                    && Carbon::parse($bundle->opening_time)->isFuture();
            });

        if (!$order) {
            return response()->json(['status' => true, 'data' => null]);
        }

        $detail = $order->details->first();
        $bundle = $detail->bundle;

        $now             = Carbon::now();
        $collectionStart = Carbon::parse($bundle->opening_time);
        $collectionEnd   = $bundle->ended_time ? Carbon::parse($bundle->ended_time) : null;
        $secondsUntil    = max(0, $now->diffInSeconds($collectionStart, false));

        // "Collection starts at 18:00 tomorrow" vs "Collection starts in HH:MM:SS"
        if ($collectionStart->isToday()) {
            $displayText = 'Collection starts at ' . $collectionStart->format('H:i') . ' today';
        } elseif ($collectionStart->isTomorrow()) {
            $displayText = 'Collection starts at ' . $collectionStart->format('H:i') . ' tomorrow';
        } else {
            $displayText = 'Collection starts on ' . $collectionStart->format('d M') . ' at ' . $collectionStart->format('H:i');
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'order_id'                 => $order->id,
                'bundle_id'                => $bundle->id,
                'bundle_name'              => $bundle->name,
                'bundle_image'             => $bundle->image ? asset($bundle->image) : null,
                'collection_start'         => $collectionStart->toIso8601String(),
                'collection_end'           => $collectionEnd?->toIso8601String(),
                'status'                   => $order->status,
                'display_text'             => $displayText,
                'seconds_until_collection' => $secondsUntil,
            ],
        ]);
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
            'payment_type'          => 'nullable|in:whish_money,omt_pay,bank,bank_transfer',
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

            // ── Loyalty discount ───────────────────────────────────────────
            $loyaltyService         = new LoyaltyService();
            $loyaltyInfo            = $loyaltyService->checkAndApplyRedemption($customer);
            $netAfterBundleDiscount = $totalSub - $totalDiscount;
            $loyaltyDiscount        = round($netAfterBundleDiscount * $loyaltyInfo['total_discount_pct'] / 100, 2);
            $totalAmount            = max(0, $netAfterBundleDiscount - $loyaltyDiscount);
            // ───────────────────────────────────────────────────────────────

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
                    'customer_id'        => $customer->id,
                    'items'              => $orderItems,
                    'address'            => $request->input('address', ''),
                    'name'               => $orderName,
                    'phone'              => $orderPhone,
                    'sub_total'          => $totalSub,
                    'total_discount'     => $totalDiscount,
                    'loyalty_discount'   => $loyaltyDiscount,
                    'points_redeemed'    => $loyaltyInfo['points_to_deduct'],
                    'has_bonus_discount' => $loyaltyInfo['bonus_used'],
                ],
            ]);

            $paymentService = new PaymentService();

            if ($paymentType === 'bank' || $paymentType === 'bank_transfer') {
                $paymentResult = $paymentService->initiateBankPayment($transaction);
            } else {
                $paymentResult = $paymentService->initiatePayment($transaction, [
                    'items' => $orderItems,
                    'customer' => $customer,
                ]);
            }

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
