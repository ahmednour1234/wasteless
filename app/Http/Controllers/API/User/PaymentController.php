<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Setting;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentController extends Controller
{
    public function successCallback(Request $request)
    {
        $externalId = $request->input('externalId');
        
        if (!$externalId) {
            return response()->json([
                'status' => false,
                'message' => 'externalId is required',
            ], 400);
        }

        $transaction = Transaction::where('external_id', $externalId)->first();

        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        if ($transaction->status === Transaction::STATUS_SUCCESS && $transaction->order_id) {
            return response()->json([
                'status' => true,
                'message' => 'Order already processed',
                'order_id' => $transaction->order_id,
            ]);
        }

        $paymentService = new PaymentService();
        $statusResult = $paymentService->checkPaymentStatus($transaction);

        if (!$statusResult['success'] || $statusResult['status'] !== 'success') {
            return response()->json([
                'status' => false,
                'message' => 'Payment verification failed',
            ], 400);
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

            return response()->json([
                'status' => true,
                'message' => 'Order created successfully',
                'order_id' => $order->id,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            
            $transaction->update(['status' => Transaction::STATUS_FAILED]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function failureCallback(Request $request)
    {
        $externalId = $request->input('externalId');
        
        if (!$externalId) {
            return response()->json([
                'status' => false,
                'message' => 'externalId is required',
            ], 400);
        }

        $transaction = Transaction::where('external_id', $externalId)->first();

        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        $transaction->update([
            'status' => Transaction::STATUS_FAILED,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Transaction marked as failed',
        ]);
    }

    public function successRedirect(Request $request)
    {
        $externalId = $request->input('externalId');
        
        if (!$externalId) {
            return response()->json([
                'status' => false,
                'message' => 'externalId is required',
            ], 400);
        }

        $transaction = Transaction::where('external_id', $externalId)->first();

        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        if ($transaction->status === Transaction::STATUS_SUCCESS && $transaction->order_id) {
            return response()->json([
                'status' => true,
                'message' => 'Order already processed',
                'order_id' => $transaction->order_id,
                'order' => $transaction->order,
            ]);
        }

        $paymentService = new PaymentService();
        $statusResult = $paymentService->checkPaymentStatus($transaction);

        if (!$statusResult['success'] || $statusResult['status'] !== 'success') {
            return response()->json([
                'status' => false,
                'message' => 'Payment verification failed',
            ], 400);
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

            return response()->json([
                'status' => true,
                'message' => 'Order created successfully',
                'order_id' => $order->id,
                'order' => $order,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            
            $transaction->update(['status' => Transaction::STATUS_FAILED]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkStatus($transactionId)
    {
        $transaction = Transaction::findOrFail($transactionId);

        $paymentService = new PaymentService();
        $statusResult = $paymentService->checkPaymentStatus($transaction);

        return response()->json([
            'status' => true,
            'transaction' => [
                'id' => $transaction->id,
                'external_id' => $transaction->external_id,
                'status' => $transaction->status,
                'collect_status' => $transaction->collect_status,
                'payer_phone_number' => $transaction->payer_phone_number,
                'order_id' => $transaction->order_id,
            ],
            'payment_status' => $statusResult,
        ]);
    }
}
