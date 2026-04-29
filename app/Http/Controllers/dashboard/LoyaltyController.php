<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Models\OrderDetail;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
    /**
     * Overview: list all customers with their loyalty info.
     */
    public function index(Request $request)
    {
        $permissions = session('permissions');
        if (!isset($permissions['Customer']) || !in_array('read', $permissions['Customer']['actions'])) {
            abort(403, 'Unauthorized action.');
        }

        $loyaltyService = new LoyaltyService();

        $query = Customer::query()
            ->withCount(['loyaltyTransactions as earned_transactions_count' => function ($q) {
                $q->where('type', 'earned');
            }])
            ->withSum(['loyaltyTransactions as total_points_earned' => function ($q) {
                $q->where('type', 'earned');
            }], 'points');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($tier = $request->input('tier')) {
            [$min, $max] = match ($tier) {
                'silver'   => [0, LoyaltyService::SILVER_MAX],
                'gold'     => [LoyaltyService::SILVER_MAX + 1, LoyaltyService::GOLD_MAX],
                'platinum' => [LoyaltyService::GOLD_MAX + 1, PHP_INT_MAX],
                default    => [0, PHP_INT_MAX],
            };
            $query->where('loyalty_points', '>=', $min);
            if ($max !== PHP_INT_MAX) {
                $query->where('loyalty_points', '<=', $max);
            }
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        // Summary stats
        $totalCustomers  = Customer::count();
        $silverCount     = Customer::where('loyalty_points', '<=', LoyaltyService::SILVER_MAX)->count();
        $goldCount       = Customer::whereBetween('loyalty_points', [LoyaltyService::SILVER_MAX + 1, LoyaltyService::GOLD_MAX])->count();
        $platinumCount   = Customer::where('loyalty_points', '>', LoyaltyService::GOLD_MAX)->count();
        $totalPoints     = Customer::sum('loyalty_points');
        $totalRedeemed   = LoyaltyTransaction::where('type', 'redeemed')->sum(DB::raw('ABS(points)'));

        return view('content.loyalty.index', compact(
            'customers',
            'loyaltyService',
            'totalCustomers',
            'silverCount',
            'goldCount',
            'platinumCount',
            'totalPoints',
            'totalRedeemed'
        ));
    }

    /**
     * Customer loyalty detail view.
     */
    public function show(Customer $customer)
    {
        $permissions = session('permissions');
        if (!isset($permissions['Customer']) || !in_array('read', $permissions['Customer']['actions'])) {
            abort(403, 'Unauthorized action.');
        }

        $loyaltyService = new LoyaltyService();

        $transactions = LoyaltyTransaction::where('customer_id', $customer->id)
            ->latest()
            ->paginate(15);

        $bagsSaved = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.customer_id', $customer->id)
            ->sum('order_details.quantity');

        $moneySaved = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.customer_id', $customer->id)
            ->selectRaw('COALESCE(SUM(order_details.discount * order_details.quantity), 0) as total')
            ->value('total');

        $nextTier = $loyaltyService->nextTierInfo($customer->loyalty_points ?? 0);

        return view('content.loyalty.show', compact(
            'customer',
            'loyaltyService',
            'transactions',
            'bagsSaved',
            'moneySaved',
            'nextTier'
        ));
    }
}
