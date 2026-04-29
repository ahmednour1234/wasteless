<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use Illuminate\Support\Carbon;

class LoyaltyService
{
    // ─── Tier thresholds ───────────────────────────────────────────────────────
    public const SILVER_MAX   = 20000;
    public const GOLD_MAX     = 65000;

    // ─── Earning rates (points per $0.01 spent) ────────────────────────────────
    public const SILVER_RATE   = 2;
    public const GOLD_RATE     = 3;
    public const PLATINUM_RATE = 5;

    // ─── Redemption constants ──────────────────────────────────────────────────
    public const REDEEM_THRESHOLD    = 9000;   // points required to redeem
    public const REDEEM_DISCOUNT_PCT = 10;     // % discount when redeeming
    public const BONUS_DISCOUNT_PCT  = 10;     // % discount on the bonus order
    public const BONUS_DAYS          = 3;      // days the bonus window stays open

    // ──────────────────────────────────────────────────────────────────────────
    // Public helpers
    // ──────────────────────────────────────────────────────────────────────────

    /** Return the tier name for a given points balance. */
    public function getTier(int $points): string
    {
        if ($points >= self::GOLD_MAX + 1) return 'platinum';
        if ($points >= self::SILVER_MAX + 1) return 'gold';
        return 'silver';
    }

    /** Return the earning rate (pts per $0.01) for a given balance. */
    public function getPointsRate(int $points): int
    {
        if ($points >= self::GOLD_MAX + 1) return self::PLATINUM_RATE;
        if ($points >= self::SILVER_MAX + 1) return self::GOLD_RATE;
        return self::SILVER_RATE;
    }

    /**
     * Calculate how many points a customer will earn for a given order amount.
     *
     * @param  float  $amountPaid    The net amount charged (after all discounts, excl. loyalty).
     * @param  int    $currentPoints The customer's current balance (determines rate).
     */
    public function calculatePointsToEarn(float $amountPaid, int $currentPoints): int
    {
        $cents = (int) round($amountPaid * 100);
        $rate  = $this->getPointsRate($currentPoints);
        return $cents * $rate;
    }

    /**
     * Check whether a customer qualifies for a redemption/bonus discount
     * and return the amounts to apply.  Does NOT write to the database.
     *
     * @return array{
     *   redeem_discount_pct: int,
     *   bonus_discount_pct: int,
     *   total_discount_pct: int,
     *   points_to_deduct: int,
     *   has_bonus: bool,
     *   bonus_used: bool
     * }
     */
    public function checkAndApplyRedemption(Customer $customer): array
    {
        $points         = $customer->loyalty_points ?? 0;
        $bonusExpiresAt = $customer->loyalty_bonus_expires_at;

        $bonusActive      = $bonusExpiresAt && Carbon::now()->lessThan($bonusExpiresAt);
        $canRedeem        = $points >= self::REDEEM_THRESHOLD;

        $redeemPct  = $canRedeem    ? self::REDEEM_DISCOUNT_PCT : 0;
        $bonusPct   = $bonusActive  ? self::BONUS_DISCOUNT_PCT  : 0;
        $pointsOut  = $canRedeem    ? self::REDEEM_THRESHOLD    : 0;

        return [
            'redeem_discount_pct'  => $redeemPct,
            'bonus_discount_pct'   => $bonusPct,
            'total_discount_pct'   => $redeemPct + $bonusPct,
            'points_to_deduct'     => $pointsOut,
            'has_bonus'            => $canRedeem,   // a new bonus window will open
            'bonus_used'           => $bonusActive, // existing bonus being consumed
        ];
    }

    /**
     * Deduct redeemed points, clear any consumed bonus window, open a new bonus
     * window if a fresh redemption happened, and log everything.
     * Call this inside a DB transaction after the Order has been created.
     */
    public function applyRedemption(Customer $customer, Order $order): void
    {
        $pointsToDeduct = $order->points_redeemed;
        $hasBonusDiscount = (bool) $order->has_bonus_discount;

        // If the existing bonus window was used, clear it
        if ($hasBonusDiscount) {
            $customer->loyalty_bonus_expires_at = null;

            LoyaltyTransaction::create([
                'customer_id'  => $customer->id,
                'order_id'     => $order->id,
                'type'         => 'bonus',
                'points'       => 0,
                'balance_after'=> $customer->loyalty_points - $pointsToDeduct,
                'description'  => 'Bonus 10% discount applied on order #' . $order->id,
            ]);
        }

        // Deduct redeemed points and open a new bonus window
        if ($pointsToDeduct > 0) {
            $newBalance = max(0, ($customer->loyalty_points ?? 0) - $pointsToDeduct);
            $customer->loyalty_points = $newBalance;
            $customer->loyalty_bonus_expires_at = Carbon::now()->addDays(self::BONUS_DAYS);

            LoyaltyTransaction::create([
                'customer_id'  => $customer->id,
                'order_id'     => $order->id,
                'type'         => 'redeemed',
                'points'       => -$pointsToDeduct,
                'balance_after'=> $newBalance,
                'description'  => 'Redeemed ' . $pointsToDeduct . ' points for 10% discount on order #' . $order->id,
            ]);
        }

        $customer->save();
    }

    /**
     * Award earned points for a completed order and log the transaction.
     * Call this inside a DB transaction after applyRedemption().
     *
     * Points are earned on: sub_total - total_discount - loyalty_discount
     */
    public function awardPoints(Customer $customer, Order $order): void
    {
        $amountPaid = max(0, $order->sub_total - $order->total_discount - $order->loyalty_discount);

        if ($amountPaid <= 0) {
            return;
        }

        $currentPoints = $customer->loyalty_points ?? 0;
        $earned        = $this->calculatePointsToEarn($amountPaid, $currentPoints);

        if ($earned <= 0) {
            return;
        }

        $newBalance = $currentPoints + $earned;
        $customer->loyalty_points = $newBalance;
        $customer->save();

        LoyaltyTransaction::create([
            'customer_id'  => $customer->id,
            'order_id'     => $order->id,
            'type'         => 'earned',
            'points'       => $earned,
            'balance_after'=> $newBalance,
            'description'  => 'Earned ' . $earned . ' points for order #' . $order->id,
        ]);
    }

    /**
     * Return info about the tier the customer will reach next, and how many
     * points are needed to get there.  Returns null values if already Platinum.
     *
     * @return array{tier: string|null, points_needed: int|null}
     */
    public function nextTierInfo(int $currentPoints): array
    {
        if ($currentPoints <= self::SILVER_MAX) {
            return [
                'tier'          => 'gold',
                'points_needed' => self::SILVER_MAX + 1 - $currentPoints,
            ];
        }
        if ($currentPoints <= self::GOLD_MAX) {
            return [
                'tier'          => 'platinum',
                'points_needed' => self::GOLD_MAX + 1 - $currentPoints,
            ];
        }
        return ['tier' => null, 'points_needed' => null];
    }
}
