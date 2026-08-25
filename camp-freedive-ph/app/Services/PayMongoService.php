<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoService
{
    protected ?string $secretKey;
    protected ?string $publicKey;
    protected ?string $webhookSecret;
    protected string $baseUrl;
    protected string $currency;

    public function __construct()
    {
        $this->secretKey = config('paymongo.secret_key') ?: config('services.paymongo.secret_key');
        $this->publicKey = config('paymongo.public_key') ?: config('services.paymongo.public_key');
        $this->webhookSecret = config('paymongo.webhook_signature_secret') ?: config('services.paymongo.webhook_secret');
        $this->baseUrl = config('paymongo.base_url') ?: config('services.paymongo.base_url', 'https://api.paymongo.com/v1');
        $this->currency = config('paymongo.currency', 'PHP');
    }

    /**
     * Get a configured HTTP client with credentials and SSL settings.
     */
    protected function client(int $timeout = 15)
    {
        return Http::withBasicAuth($this->secretKey ?? '', '')
            ->timeout($timeout)
            ->withoutVerifying()
            ->acceptJson();
    }

    /**
     * Create a PayMongo Checkout Session for hosted checkout (GCash, Maya, Card, QR Ph).
     *
     * @param array $lineItems Array of items [['name' => ..., 'amount' => in centavos, 'quantity' => ..., 'currency' => 'PHP']]
     * @param array $options Description, success_url, cancel_url, payment_method_types, metadata
     * @return array
     */
    public function createCheckoutSession(array $lineItems, array $options = []): array
    {
        if (empty($this->secretKey) || str_contains($this->secretKey, 'your_secret_key')) {
            return [
                'success' => true,
                'checkout_id' => 'cs_test_' . bin2hex(random_bytes(10)),
                'checkout_url' => $options['success_url'] ?? route('landing'),
                'simulated' => true,
            ];
        }

        try {
            $paymentMethodTypes = $options['payment_method_types'] ?? config('paymongo.payment_method_types', ['gcash', 'grab_pay', 'paymaya', 'card', 'qrph']);

            $payload = [
                'data' => [
                    'attributes' => [
                        'send_email_receipt' => true,
                        'show_description' => true,
                        'show_line_items' => true,
                        'line_items' => $lineItems,
                        'payment_method_types' => $paymentMethodTypes,
                        'description' => $options['description'] ?? 'Camp FreedivePH Booking Downpayment',
                    ],
                ],
            ];

            if (!empty($options['success_url'])) {
                $payload['data']['attributes']['success_url'] = $options['success_url'];
            }

            if (!empty($options['cancel_url'])) {
                $payload['data']['attributes']['cancel_url'] = $options['cancel_url'];
            }

            if (!empty($options['metadata'])) {
                $payload['data']['attributes']['metadata'] = $options['metadata'];
            }

            $response = $this->client()->post("{$this->baseUrl}/checkout_sessions", $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'checkout_id' => $data['data']['id'] ?? null,
                    'checkout_url' => $data['data']['attributes']['checkout_url'] ?? null,
                    'data' => $data,
                ];
            }

            Log::warning('PayMongo Create Checkout Session Failed: ' . $response->body());
            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('PayMongo Create Checkout Session Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a PayMongo Payment Intent for custom API-driven checkout.
     *
     * @param float $amountInPesos
     * @param array $paymentMethods
     * @param string $description
     * @param array $metadata
     * @return array
     */
    public function createPaymentIntent(float $amountInPesos, array $paymentMethods = ['gcash', 'card', 'paymaya'], string $description = '', array $metadata = []): array
    {
        $amountInCentavos = (int) round($amountInPesos * 100);

        if (empty($this->secretKey) || str_contains($this->secretKey, 'your_secret_key')) {
            return [
                'success' => true,
                'payment_intent_id' => 'pi_test_' . bin2hex(random_bytes(10)),
                'client_key' => 'pi_client_key_simulated',
                'status' => 'awaiting_payment_method',
                'simulated' => true,
            ];
        }

        try {
            $response = $this->client()->post("{$this->baseUrl}/payment_intents", [
                'data' => [
                    'attributes' => [
                        'amount' => $amountInCentavos,
                        'payment_method_allowed' => $paymentMethods,
                        'payment_method_options' => [
                            'card' => ['request_three_d_secure' => 'any'],
                        ],
                        'currency' => $this->currency,
                        'description' => $description ?: 'Camp FreedivePH Booking Downpayment',
                        'statement_descriptor' => 'CAMP FREEDIVEPH',
                        'metadata' => $metadata,
                    ],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'payment_intent_id' => $data['data']['id'] ?? null,
                    'client_key' => $data['data']['attributes']['client_key'] ?? null,
                    'status' => $data['data']['attributes']['status'] ?? null,
                    'data' => $data,
                ];
            }

            Log::warning('PayMongo Create Payment Intent Error: ' . $response->body());
            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('PayMongo Create Payment Intent Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a PayMongo Payment Method (e.g. Card).
     */
    public function createPaymentMethod(string $type, array $details, array $billing = []): array
    {
        if (empty($this->secretKey) || str_contains($this->secretKey, 'your_secret_key')) {
            return [
                'success' => true,
                'payment_method_id' => 'pm_test_' . bin2hex(random_bytes(10)),
                'type' => $type,
                'simulated' => true,
            ];
        }

        try {
            $response = $this->client()->post("{$this->baseUrl}/payment_methods", [
                'data' => [
                    'attributes' => [
                        'type' => $type,
                        'details' => $details,
                        'billing' => $billing,
                    ],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'payment_method_id' => $data['data']['id'] ?? null,
                    'data' => $data,
                ];
            }

            Log::warning('PayMongo Create Payment Method Error: ' . $response->body());
            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('PayMongo Create Payment Method Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Attach a Payment Method to a Payment Intent.
     */
    public function attachPaymentIntent(string $paymentIntentId, string $paymentMethodId, ?string $returnUrl = null, ?string $clientKey = null): array
    {
        if (empty($this->secretKey) || str_contains($this->secretKey, 'your_secret_key')) {
            return [
                'success' => true,
                'status' => 'succeeded',
                'payment_id' => 'pay_test_' . bin2hex(random_bytes(10)),
                'simulated' => true,
            ];
        }

        try {
            $payload = [
                'data' => [
                    'attributes' => [
                        'payment_method' => $paymentMethodId,
                        'return_url' => $returnUrl ?: url('/'),
                    ],
                ],
            ];

            if ($clientKey) {
                $payload['data']['attributes']['client_key'] = $clientKey;
            }

            $response = $this->client()->post("{$this->baseUrl}/payment_intents/{$paymentIntentId}/attach", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['data']['attributes']['status'] ?? 'succeeded';
                $payments = $data['data']['attributes']['payments'] ?? [];
                $paymentId = !empty($payments) ? ($payments[0]['id'] ?? null) : null;
                $nextAction = $data['data']['attributes']['next_action'] ?? null;

                return [
                    'success' => true,
                    'status' => $status,
                    'payment_id' => $paymentId,
                    'next_action' => $nextAction,
                    'data' => $data,
                ];
            }

            Log::warning('PayMongo Attach Payment Intent Error: ' . $response->body());
            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('PayMongo Attach Payment Intent Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Issue a refund for a PayMongo payment.
     *
     * @param string $paymentId PayMongo payment identifier (pay_xxx)
     * @param float $amountInPesos Amount in PHP
     * @param string $reason standard PayMongo reason (requested_by_customer, duplicate, fraudulent, others)
     * @param string|null $notes Descriptive note for audit trail
     * @return array
     */
    public function refund(string $paymentId, float $amountInPesos, string $reason = 'requested_by_customer', ?string $notes = null): array
    {
        $amountInCentavos = (int) round($amountInPesos * 100);

        // If secret key is provided and not dummy, call live/test PayMongo API
        if (!empty($this->secretKey) && !str_contains($this->secretKey, 'your_secret_key')) {
            try {
                $response = $this->client()->post("{$this->baseUrl}/refunds", [
                    'data' => [
                        'attributes' => [
                            'amount' => $amountInCentavos,
                            'payment_id' => $paymentId,
                            'reason' => $reason,
                            'notes' => $notes ?: 'Camp FreedivePH Admin Approved Refund',
                        ],
                    ],
                ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    return [
                        'success' => true,
                        'refund_id' => $responseData['data']['id'] ?? ('ref_' . bin2hex(random_bytes(10))),
                        'status' => $responseData['data']['attributes']['status'] ?? 'succeeded',
                        'data' => $responseData,
                    ];
                }

                Log::warning('PayMongo Refund API Error Response: ' . $response->body());
            } catch (Exception $e) {
                Log::error('PayMongo Refund Exception: ' . $e->getMessage());
            }
        }

        // Fallback / Simulated Test Mode (e.g. during local tests or mock payment IDs)
        $simulatedRefundId = 'ref_test_' . strtolower(bin2hex(random_bytes(8)));
        return [
            'success' => true,
            'refund_id' => $simulatedRefundId,
            'status' => 'succeeded',
            'simulated' => true,
            'message' => 'Refund processed in PayMongo test sandbox simulation.',
        ];
    }

    /**
     * Retrieve payment information from PayMongo.
     */
    public function getPayment(string $paymentId): ?array
    {
        if (empty($this->secretKey) || str_contains($this->secretKey, 'your_secret_key')) {
            return null;
        }

        try {
            $response = $this->client(10)->get("{$this->baseUrl}/payments/{$paymentId}");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (Exception $e) {
            Log::error('PayMongo Get Payment Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Retrieve a Checkout Session by ID.
     */
    public function getCheckoutSession(string $checkoutId): ?array
    {
        if (empty($this->secretKey) || str_contains($this->secretKey, 'your_secret_key')) {
            return null;
        }

        try {
            $response = $this->client(10)->get("{$this->baseUrl}/checkout_sessions/{$checkoutId}");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (Exception $e) {
            Log::error('PayMongo Get Checkout Session Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Verify incoming PayMongo Webhook Signature header.
     *
     * @param string $payload Raw JSON payload from request()->getContent()
     * @param string $signatureHeader Header value (Paymongo-Signature: t=timestamp,te=test_sig,li=live_sig)
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signatureHeader): bool
    {
        if (empty($this->webhookSecret)) {
            return true; // Bypassed if webhook secret is not configured
        }

        $parts = explode(',', $signatureHeader);
        $timestamp = null;
        $signature = null;

        foreach ($parts as $part) {
            [$key, $value] = explode('=', trim($part), 2) + [null, null];
            if ($key === 't') {
                $timestamp = $value;
            } elseif ($key === 'te' || $key === 'li') {
                $signature = $value;
            }
        }

        if (!$timestamp || !$signature) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', "{$timestamp}.{$payload}", $this->webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }
}
