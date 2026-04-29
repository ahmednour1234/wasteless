<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\LoyaltyService;

class LoyaltyResource extends JsonResource
{
    protected array $extra;

    public function __construct($resource, array $extra = [])
    {
        parent::__construct($resource);
        $this->extra = $extra;
    }

    public function toArray(Request $request): array
    {
        $loyaltyService = new LoyaltyService();
        $points         = $this->loyalty_points ?? 0;
        $tier           = $loyaltyService->getTier($points);
        $nextTier       = $loyaltyService->nextTierInfo($points);
        $bonusExpires   = $this->loyalty_bonus_expires_at;
        $bonusActive    = $bonusExpires && now()->lessThan($bonusExpires);

        return [
            'points'                   => $points,
            'tier'                     => $tier,
            'next_tier'                => $nextTier['tier'],
            'points_to_next_tier'      => $nextTier['points_needed'],
            'can_redeem'               => $points >= LoyaltyService::REDEEM_THRESHOLD,
            'redeem_points_required'   => LoyaltyService::REDEEM_THRESHOLD,
            'bonus_discount_available' => $bonusActive,
            'bonus_expires_at'         => $bonusExpires?->toIso8601String(),
            'bags_saved'               => $this->extra['bags_saved']   ?? 0,
            'money_saved'              => $this->extra['money_saved']  ?? 0,
            'recent_transactions'      => $this->extra['recent_transactions'] ?? [],
        ];
    }
}
