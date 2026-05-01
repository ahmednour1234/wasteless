<?php
// app/Http/Controllers/API/User/AuthUserController.php

namespace App\Http\Controllers\API\User;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\OrderDetail;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthUserController extends Controller
{
    /* ====== تسجيل جديد ====== */
    public function register(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:customers,email',
            'phone'    => 'required|string|unique:customers,phone',
            'password' => 'required|string|min:6',
            'img'      => 'nullable|image',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $data = $v->validated();

        if ($request->hasFile('img')) {
            $data['img'] = FileHelper::uploadImage($request->file('img'), 'uploads/customers');
        }

        $customer = Customer::create($data);
        $token    = $customer->createToken('user-token')->plainTextToken;

        return response()->json([
            'message'  => 'Registered successfully.',
            'token'    => $token,
            'customer' => new CustomerResource($customer),
        ], 201);
    }

    /* ====== تسجيل الدخول ====== */
    public function login(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $customer = Customer::where('email', $request->email)->first();

        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            return response()->json(['message' => 'Invalid credentials'], 403);
        }

        $token = $customer->createToken('user-token')->plainTextToken;

        return response()->json([
            'message'  => 'Logged in.',
            'token'    => $token,
            'customer' => new CustomerResource($customer),
        ]);
    }

    /* ====== بيانات المستخدم الحالي ====== */
    public function me(Request $request)
    {
        return new CustomerResource($request->user());
    }

    /* ====== تحديث بيانات المستخدم ====== */
    public function update(Request $request)
    {
        $customer = $request->user();   // مستخدم مصدَّق

        $v = Validator::make($request->all(), [
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:customers,email,' . $customer->id,
            'phone'    => 'sometimes|string|unique:customers,phone,' . $customer->id,
            'password' => 'sometimes|string|min:6',
            'img'      => 'sometimes|image',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $data = $v->validated();

        if ($request->hasFile('img')) {
            $data['img'] = FileHelper::uploadImage($request->file('img'), 'uploads/customers');
        }

        $customer->update($data);

        return response()->json([
            'message'  => 'Profile updated.',
            'customer' => new CustomerResource($customer),
        ]);
    }

    /* ====== تسجيل الخروج ====== */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    /* ====== Account Overview (Profile Screen) ====== */
    public function account(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $loyaltyService = new LoyaltyService();
        $points         = (int) ($customer->loyalty_points ?? 0);
        $tier           = $loyaltyService->getTier($points);
        $nextTier       = $loyaltyService->nextTierInfo($points);

        // Tier progress percentage within the current tier range
        if ($tier === 'silver') {
            $tierProgressPct = min(100, round(($points / LoyaltyService::SILVER_MAX) * 100, 1));
        } elseif ($tier === 'gold') {
            $range           = LoyaltyService::GOLD_MAX - LoyaltyService::SILVER_MAX;
            $tierProgressPct = min(100, round((($points - LoyaltyService::SILVER_MAX) / $range) * 100, 1));
        } else {
            $tierProgressPct = 100.0;
        }

        // Bags saved: total quantity across all orders
        $bagsSaved = (int) OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.customer_id', $customer->id)
            ->sum('order_details.quantity');

        // Money saved: SUM(discount * quantity) across all order details
        $moneySaved = (float) OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.customer_id', $customer->id)
            ->selectRaw('COALESCE(SUM(order_details.discount * order_details.quantity), 0) as total')
            ->value('total');

        $bonusExpires = $customer->loyalty_bonus_expires_at;

        return response()->json([
            'customer' => [
                'id'    => $customer->id,
                'name'  => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'img'   => $customer->img ? asset($customer->img) : null,
            ],
            'loyalty' => [
                'points'                   => $points,
                'tier'                     => $tier,
                'tier_progress_percent'    => $tierProgressPct,
                'next_tier'                => $nextTier['tier'],
                'points_to_next_tier'      => $nextTier['points_needed'],
                'can_redeem'               => $points >= LoyaltyService::REDEEM_THRESHOLD,
                'bonus_discount_available' => $bonusExpires && now()->lessThan($bonusExpires),
                'bonus_expires_at'         => $bonusExpires?->toIso8601String(),
            ],
            'stats' => [
                'bags_saved'  => $bagsSaved,
                'money_saved' => round($moneySaved, 2),
            ],
            'menu' => [
                ['key' => 'my_profile',       'label' => 'My profile'],
                ['key' => 'orders',            'label' => 'Orders'],
                ['key' => 'settings',          'label' => 'Settings'],
                ['key' => 'help',              'label' => 'Help'],
                ['key' => 'payment_methods',   'label' => 'Payment methods'],
                ['key' => 'refer_a_friend',    'label' => 'Refer a friend'],
            ],
        ]);
    }
}
