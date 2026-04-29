<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoyaltyResource;
use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
    /**
     * GET /user/loyalty
     * Return the authenticated customer's loyalty dashboard.
     */
    public function index()
    {
        /** @var Customer $customer */
        $customer = Customer::findOrFail(Auth::id());

        // ── Bags saved: total quantity of bundle items from all orders ──────
        $bagsSaved = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.customer_id', $customer->id)
            ->sum('order_details.quantity');

        // ── Money saved: SUM(discount * quantity) across all order details ──
        $moneySaved = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.customer_id', $customer->id)
            ->selectRaw('COALESCE(SUM(order_details.discount * order_details.quantity), 0) as total')
            ->value('total');

        // ── Last 10 loyalty transactions ────────────────────────────────────
        $recentTransactions = LoyaltyTransaction::where('customer_id', $customer->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($tx) => [
                'id'          => $tx->id,
                'type'        => $tx->type,
                'points'      => $tx->points,
                'balance_after' => $tx->balance_after,
                'description' => $tx->description,
                'order_id'    => $tx->order_id,
                'created_at'  => $tx->created_at?->toIso8601String(),
            ]);

        return new LoyaltyResource($customer, [
            'bags_saved'           => (int) $bagsSaved,
            'money_saved'          => round((float) $moneySaved, 2),
            'recent_transactions'  => $recentTransactions,
        ]);
    }

    /**
     * GET /user/loyalty/transactions
     * Return a paginated list of all loyalty point transactions for the customer.
     */
    public function transactions(Request $request)
    {
        /** @var Customer $customer */
        $customer = Customer::findOrFail(Auth::id());

        $transactions = LoyaltyTransaction::where('customer_id', $customer->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => true,
            'data'   => $transactions->map(fn ($tx) => [
                'id'            => $tx->id,
                'type'          => $tx->type,
                'points'        => $tx->points,
                'balance_after' => $tx->balance_after,
                'description'   => $tx->description,
                'order_id'      => $tx->order_id,
                'created_at'    => $tx->created_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'per_page'     => $transactions->perPage(),
                'last_page'    => $transactions->lastPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }
}
