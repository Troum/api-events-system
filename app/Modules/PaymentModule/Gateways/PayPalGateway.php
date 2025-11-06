<?php

namespace App\Modules\PaymentModule\Gateways;

use App\Modules\PaymentModule\Contracts\PaymentGateway;
use App\Modules\PaymentModule\Contracts\PaymentResponse;
use App\Modules\PaymentModule\Contracts\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalGateway implements PaymentGateway
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->baseUrl = config('services.paypal.test_mode', true)
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    private function getAccessToken(): string
    {
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            Log::error('PayPal access token retrieval failed', [
                'response' => $response->body(),
            ]);
            throw new \Exception('Failed to get PayPal access token');
        }

        return $response->json()['access_token'];
    }

    public function createPayment(float $amount, string $description, array $metadata = []): PaymentResponse
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'description' => $description,
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => number_format($amount, 2, '.', ''),
                        ],
                        'custom_id' => $metadata['booking_id'] ?? null,
                    ],
                ],
                'application_context' => [
                    'return_url' => config('app.url') . '/payment/callback/paypal',
                    'cancel_url' => config('app.url') . '/payment/cancel',
                    'brand_name' => config('app.name'),
                    'landing_page' => 'BILLING',
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        if (!$response->successful()) {
            Log::error('PayPal payment creation failed', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);
            throw new \Exception('Failed to create PayPal payment');
        }

        $data = $response->json();

        // Находим ссылку для редиректа
        $approveUrl = collect($data['links'])->firstWhere('rel', 'approve')['href'] ?? '';

        return new PaymentResponse(
            paymentId: $data['id'],
            paymentUrl: $approveUrl,
            status: strtolower($data['status']),
        );
    }

    public function handleCallback(Request $request): PaymentStatus
    {
        $orderId = $request->input('token');
        $accessToken = $this->getAccessToken();

        // Захватываем платеж
        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

        if (!$response->successful()) {
            Log::error('PayPal payment capture failed', [
                'order_id' => $orderId,
                'response' => $response->body(),
            ]);
            throw new \Exception('Failed to capture PayPal payment');
        }

        $data = $response->json();

        $status = match ($data['status'] ?? null) {
            'COMPLETED' => 'success',
            'APPROVED' => 'pending',
            default => 'failed',
        };

        return new PaymentStatus(
            status: $status,
            transactionId: $data['id'],
        );
    }
}

