<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Setting;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;
use Exception;

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

    public function processTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status === Transaction::STATUS_SUCCESS && $transaction->order_id) {
            return redirect()->back()->with('info', 'Transaction already processed');
        }

        $paymentService = new PaymentService();
        $statusResult = $paymentService->checkPaymentStatus($transaction);

        if (!$statusResult['success']) {
            return redirect()->back()->with('error', $statusResult['message'] ?? 'Payment verification failed');
        }

        if ($statusResult['status'] !== 'success') {
            return redirect()->back()->with('error', 'Payment is not successful. Status: ' . $statusResult['status']);
        }

        DB::beginTransaction();
        try {
            $metadata = $transaction->metadata;
            $customerId = $metadata['customer_id'] ?? null;
            $orderItems = $metadata['items'] ?? [];

            if (!$customerId || empty($orderItems)) {
                throw new Exception('Invalid transaction metadata');
            }

            $totalAmount = ($metadata['sub_total'] ?? 0) - ($metadata['total_discount'] ?? 0);
            $settings = Setting::first();
            $commissionPercentage = $settings->commission_percentage ?? 0;
            $commissionAmount = ($totalAmount * $commissionPercentage) / 100;

            $order = Order::create([
                'customer_id'         => $customerId,
                'status'              => 'pending',
                'sub_total'           => $metadata['sub_total'] ?? 0,
                'total_discount'      => $metadata['total_discount'] ?? 0,
                'delivery'            => 0,
                'commission_percentage' => $commissionPercentage,
                'commission_amount'   => $commissionAmount,
                'address'             => $metadata['address'] ?? '',
                'name'                => $metadata['name'] ?? '',
                'phone'               => $metadata['phone'] ?? '',
            ]);

            foreach ($orderItems as $itemData) {
                $bundle = $itemData['bundle'];
                
                OrderDetail::create([
                    'order_id'    => $order->id,
                    'bundle_id'   => $bundle['id'],
                    'company_id'  => $bundle['company_id'],
                    'branch_id'   => $bundle['branch_id'],
                    'category_id' => $bundle['category_id'] ?? null,
                    'quantity'    => $itemData['quantity'],
                    'price'       => $itemData['price'],
                    'discount'    => $itemData['discount'],
                    'total'       => $itemData['total'],
                    'bundles'     => $itemData['snapshot'],
                    'status'      => 'pending',
                ]);

                $bundleModel = \App\Models\Bundle::find($bundle['id']);
                if ($bundleModel) {
                    $bundleModel->decrement('stock', $itemData['quantity']);
                }
            }

            $transaction->update([
                'order_id'             => $order->id,
                'status'               => Transaction::STATUS_SUCCESS,
                'commission_percentage' => $commissionPercentage,
                'commission_amount'    => $commissionAmount,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Transaction processed successfully. Order #' . $order->id . ' created.');
        } catch (Exception $e) {
            DB::rollBack();
            
            $transaction->update(['status' => Transaction::STATUS_FAILED]);

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
