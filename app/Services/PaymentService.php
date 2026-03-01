<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentService
{
    public function initiatePayment(Transaction $transaction, array $orderData): array
    {
        $baseUrl = $this->getBaseUrl();
        $url = $baseUrl . 'payment/whish';

        $payload = [
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'invoice' => $transaction->invoice ?? 'Order Payment',
            'externalId' => (int) $transaction->external_id,
            'successCallbackUrl' => $transaction->success_callback_url,
            'failureCallbackUrl' => $transaction->failure_callback_url,
            'successRedirectUrl' => $transaction->success_redirect_url,
            'failureRedirectUrl' => $transaction->failure_redirect_url,
        ];

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post($url, $payload);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === true) {
                $collectUrl = $responseData['data']['collectUrl'] ?? null;
                
                if ($collectUrl) {
                    $transaction->update([
                        'collect_url' => $collectUrl,
                        'status' => Transaction::STATUS_PENDING,
                    ]);

                    return [
                        'success' => true,
                        'collect_url' => $collectUrl,
                        'transaction_id' => $transaction->id,
                    ];
                }
            }

            Log::error('Whish Payment Initiation Failed', [
                'transaction_id' => $transaction->id,
                'response' => $responseData,
            ]);

            return [
                'success' => false,
                'message' => $responseData['dialog']['message'] ?? 'Payment initiation failed',
                'code' => $responseData['code'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Whish Payment API Exception', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Payment service error: ' . $e->getMessage(),
            ];
        }
    }

    public function checkPaymentStatus(Transaction $transaction): array
    {
        $baseUrl = $this->getBaseUrl();
        $url = $baseUrl . 'payment/collect/status';

        $payload = [
            'currency' => $transaction->currency,
            'externalId' => (int) $transaction->external_id,
        ];

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post($url, $payload);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === true) {
                $data = $responseData['data'] ?? [];
                $collectStatus = $data['collectStatus'] ?? null;
                $payerPhoneNumber = $data['payerPhoneNumber'] ?? null;

                $transaction->update([
                    'collect_status' => $collectStatus,
                    'payer_phone_number' => $payerPhoneNumber,
                ]);

                if ($collectStatus === 'success') {
                    $transaction->update(['status' => Transaction::STATUS_SUCCESS]);
                } elseif ($collectStatus === 'failed') {
                    $transaction->update(['status' => Transaction::STATUS_FAILED]);
                }

                return [
                    'success' => true,
                    'status' => $collectStatus,
                    'payer_phone_number' => $payerPhoneNumber,
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['dialog']['message'] ?? 'Status check failed',
                'code' => $responseData['code'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Whish Payment Status Check Exception', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Status check error: ' . $e->getMessage(),
            ];
        }
    }

    private function getBaseUrl(): string
    {
        $env = config('services.whish.env', 'sandbox');
        
        if ($env === 'production') {
            return 'https://api.whish.money/itel-service/api/';
        }
        
        return 'https://api.sandbox.whish.money/itel-service/api/';
    }

    private function getHeaders(): array
    {
        return [
            'channel' => config('services.whish.channel'),
            'secret' => config('services.whish.secret'),
            'websiteurl' => config('services.whish.website_url'),
            'Content-Type' => 'application/json',
        ];
    }
}
