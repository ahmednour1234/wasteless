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

    // -----------------------------------------------------------------------
    // MPGS (Mastercard/NetCommerce) Hosted Checkout – bank payment
    // -----------------------------------------------------------------------

    public function initiateBankPayment(Transaction $transaction): array
    {
        $merchantId  = config('services.mpgs.merchant_id');
        $apiPassword = config('services.mpgs.api_password');
        $gatewayUrl  = rtrim(config('services.mpgs.gateway_url'), '/');
        $apiVersion  = config('services.mpgs.api_version', '61');

        $appUrl  = config('app.url');
        $baseUrl = (str_starts_with($appUrl, 'http://') ? str_replace('http://', 'https://', $appUrl) : $appUrl) . '/api';

        // Embed external_id in the return URL so we can identify the transaction
        $returnUrl = $baseUrl . '/user/payments/bank/return?external_id=' . $transaction->external_id;

        $url = "{$gatewayUrl}/api/rest/version/{$apiVersion}/merchant/{$merchantId}/session";

        $payload = [
            'apiOperation' => 'INITIATE_CHECKOUT',
            'order' => [
                'id'       => $transaction->external_id,
            ],
        ];

        try {
            $response = Http::withBasicAuth('merchant.' . $merchantId, $apiPassword)
                ->post($url, $payload);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['session']['id'])) {
                $sessionId        = $responseData['session']['id'];
                $successIndicator = $responseData['successIndicator'] ?? null;
                $checkoutUrl      = "{$gatewayUrl}/checkout/pay/{$sessionId}?checkoutVersion=1.0.0";

                $transaction->update([
                    'collect_url' => $checkoutUrl,
                    'status'      => Transaction::STATUS_PENDING,
                    'metadata'    => array_merge($transaction->metadata ?? [], [
                        'mpgs_session_id'        => $sessionId,
                        'mpgs_success_indicator' => $successIndicator,
                    ]),
                ]);

                return [
                    'success'        => true,
                    'collect_url'    => $checkoutUrl,
                    'session_id'     => $sessionId,
                    'transaction_id' => $transaction->id,
                ];
            }

            Log::error('MPGS Bank Payment Initiation Failed', [
                'transaction_id' => $transaction->id,
                'response'       => $responseData,
            ]);

            return [
                'success' => false,
                'message' => $responseData['error']['explanation'] ?? 'Bank payment initiation failed',
            ];
        } catch (Exception $e) {
            Log::error('MPGS Bank Payment API Exception', [
                'transaction_id' => $transaction->id,
                'error'          => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Bank payment service error: ' . $e->getMessage(),
            ];
        }
    }

    public function verifyBankPayment(Transaction $transaction, string $resultIndicator): array
    {
        $metadata         = $transaction->metadata ?? [];
        $successIndicator = $metadata['mpgs_success_indicator'] ?? null;

        if ($successIndicator && $resultIndicator === $successIndicator) {
            $transaction->update(['status' => Transaction::STATUS_SUCCESS]);
            return ['success' => true, 'status' => 'success'];
        }

        // If indicators don't match, query the gateway to confirm the real status
        $merchantId  = config('services.mpgs.merchant_id');
        $apiPassword = config('services.mpgs.api_password');
        $gatewayUrl  = rtrim(config('services.mpgs.gateway_url'), '/');
        $apiVersion  = config('services.mpgs.api_version', '61');

        $url = "{$gatewayUrl}/api/rest/version/{$apiVersion}/merchant/{$merchantId}/order/{$transaction->external_id}";

        try {
            $response     = Http::withBasicAuth('merchant.' . $merchantId, $apiPassword)->get($url);
            $responseData = $response->json();

            if ($response->successful()) {
                $gatewayStatus = $responseData['status'] ?? null;

                if (in_array($gatewayStatus, ['CAPTURED', 'AUTHORIZED', 'APPROVED'])) {
                    $transaction->update(['status' => Transaction::STATUS_SUCCESS]);
                    return ['success' => true, 'status' => 'success'];
                }

                $transaction->update(['status' => Transaction::STATUS_FAILED]);
                return [
                    'success' => false,
                    'status'  => 'failed',
                    'message' => 'Payment not completed. Gateway status: ' . ($gatewayStatus ?? 'unknown'),
                ];
            }
        } catch (Exception $e) {
            Log::error('MPGS Bank Payment Verify Exception', [
                'transaction_id' => $transaction->id,
                'error'          => $e->getMessage(),
            ]);
        }

        $transaction->update(['status' => Transaction::STATUS_FAILED]);
        return ['success' => false, 'status' => 'failed', 'message' => 'Payment verification failed'];
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
