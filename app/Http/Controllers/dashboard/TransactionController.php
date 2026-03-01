<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Customer;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::with('order.customer')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->payment_type, fn($q) => $q->where('payment_type', $request->payment_type))
            ->when($request->external_id, fn($q) => $q->where('external_id', 'like', '%' . $request->external_id . '%'))
            ->latest()
            ->paginate(15);

        return view('content.transactions.index', compact('transactions'));
    }

    public function show($id)
    {
        $transaction = Transaction::with('order.details.bundle')->findOrFail($id);

        return view('content.transactions.show', compact('transaction'));
    }
}
