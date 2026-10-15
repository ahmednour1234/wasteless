<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use Exception;
use Illuminate\Support\Carbon;

class PromoCodeService
{
    /**
     * Validate a promo code for a customer against an amount.
     * Returns ['promo' => PromoCode, 'discount' => float] or throws with a user-facing message.
     */
    public function validate(string $code, Customer $customer, float $amount): array
    {
        $promo = PromoCode::where('code', strtoupper(trim($code)))->first();

        if (!$promo) {
            throw new Exception('Invalid promo code');
        }

        if (!$promo->is_active) {
            throw new Exception('This promo code is inactive');
        }

        $now = Carbon::now();
        if ($promo->start_date && $now->lt($promo->start_date)) {
            throw new Exception('This promo code is not active yet');
        }
        if ($promo->end_date && $now->gt($promo->end_date)) {
            throw new Exception('This promo code has expired');
        }

        if ($promo->min_order_amount && $amount < $promo->min_order_amount) {
            throw new Exception('Minimum order amount for this promo code is ' . number_format($promo->min_order_amount, 2));
        }

        if ($promo->usage_limit !== null && $promo->usages()->count() >= $promo->usage_limit) {
            throw new Exception('This promo code has reached its usage limit');
        }

        if ($promo->usage_limit_per_user !== null
            && $promo->usages()->where('customer_id', $customer->id)->count() >= $promo->usage_limit_per_user) {
            throw new Exception('You have already used this promo code the maximum number of times');
        }

        return [
            'promo'    => $promo,
            'discount' => $this->calculateDiscount($promo, $amount),
        ];
    }

    public function calculateDiscount(PromoCode $promo, float $amount): float
    {
        if ($promo->type === PromoCode::TYPE_PERCENTAGE) {
            $discount = $amount * $promo->value / 100;
            if ($promo->max_discount) {
                $discount = min($discount, $promo->max_discount);
            }
        } else {
            $discount = $promo->value;
        }

        return round(min($discount, $amount), 2);
    }

    /** Snapshot stored in the transaction metadata at checkout. */
    public function toMetadata(?PromoCode $promo, float $discount): array
    {
        return [
            'promo_code_id'  => $promo?->id,
            'promo_code'     => $promo?->code,
            'promo_type'     => $promo?->type,
            'promo_value'    => $promo?->value,
            'promo_discount' => $promo ? $discount : 0,
        ];
    }

    /** Order columns built from the transaction metadata. */
    public static function orderAttributes(array $metadata): array
    {
        return [
            'promo_code_id'  => $metadata['promo_code_id'] ?? null,
            'promo_code'     => $metadata['promo_code'] ?? null,
            'promo_type'     => $metadata['promo_type'] ?? null,
            'promo_value'    => $metadata['promo_value'] ?? null,
            'promo_discount' => (float) ($metadata['promo_discount'] ?? 0),
        ];
    }

    /** Count the usage only once the order actually exists (after payment). */
    public static function recordUsage(Order $order): void
    {
        if (!$order->promo_code_id) {
            return;
        }

        PromoCodeUsage::firstOrCreate(
            ['order_id' => $order->id],
            [
                'promo_code_id'   => $order->promo_code_id,
                'customer_id'     => $order->customer_id,
                'discount_amount' => $order->promo_discount,
            ]
        );
    }
}
