<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Customer;
use App\Services\PromoCodeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PromoCodeController extends Controller
{
    /**
     * Check a promo code against the cart and return the discount preview.
     * Nothing is consumed here — usage is recorded only when the paid order is created.
     * Send either `items` (same shape as POST /orders) or a plain `amount`.
     */
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code'              => 'required|string|max:50',
            'items'             => 'required_without:amount|array|min:1',
            'items.*.bundle_id' => 'required_with:items|exists:bundles,id',
            'items.*.quantity'  => 'required_with:items|integer|min:1',
            'amount'            => 'required_without:items|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = Customer::findOrFail(Auth::id());

        $subTotal       = 0;
        $bundleDiscount = 0;

        if ($request->filled('items')) {
            foreach ($request->items as $item) {
                $bundle = Bundle::where('id', $item['bundle_id'])->where('active', 1)->first();
                if (!$bundle) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Bundle not found or inactive',
                    ], 404);
                }

                $finalPrice      = $bundle->price_after_discount ?? $bundle->price;
                $subTotal       += $bundle->price * $item['quantity'];
                $bundleDiscount += ($bundle->price - $finalPrice) * $item['quantity'];
            }
        } else {
            $subTotal = (float) $request->amount;
        }

        $amount = $subTotal - $bundleDiscount;

        try {
            $result = (new PromoCodeService())->validate($request->code, $customer, $amount);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $promo = $result['promo'];

        return response()->json([
            'status'  => true,
            'message' => 'Promo code applied successfully',
            'data'    => [
                'code'            => $promo->code,
                'type'            => $promo->type,
                'value'           => $promo->value,
                'max_discount'    => $promo->max_discount,
                'sub_total'       => round($subTotal, 2),
                'bundle_discount' => round($bundleDiscount, 2),
                'amount'          => round($amount, 2),
                'promo_discount'  => $result['discount'],
                'total'           => round(max(0, $amount - $result['discount']), 2),
                'end_date'        => $promo->end_date?->toIso8601String(),
            ],
        ]);
    }
}
