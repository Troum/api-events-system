<?php

namespace App\Modules\PaymentModule\Gateways;

use App\Modules\PaymentModule\Contracts\PaymentGateway;
use App\Modules\PaymentModule\Contracts\PaymentResponse;
use App\Modules\PaymentModule\Contracts\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebPayGateway implements PaymentGateway
{
    private string $merchantId;
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->merchantId = config('services.webpay.merchant_id');
        $this->secretKey = config('services.webpay.secret_key');
        $this->baseUrl = config('services.webpay.test_mode', true)
            ? 'https://test.webpay.com/api'
            : 'https://api.webpay.com';
    }

    public function createPayment(float $amount, string $description, array $metadata = []): PaymentResponse
    {
        // WebPay работает с центами
        $amountInCents = (int) ($amount * 100);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/v1/payments", [
            'merchant_id' => $this->merchantId,
            'amount' => $amountInCents,
            'currency' => 'EUR',
            'description' => $description,
            'metadata' => $metadata,
            'return_url' => config('app.url') . '/payment/callback/webpay',
            'cancel_url' => config('app.url') . '/payment/cancel',
        ]);

        if (!$response->successful()) {
            Log::error('WebPay payment creation failed', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);
            throw new \Exception('Failed to create WebPay payment');
        }

        $data = $response->json();

        return new PaymentResponse(
            paymentId: $data['payment_id'] ?? $data['id'],
            paymentUrl: $data['payment_url'] ?? $data['redirect_url'] ?? '',
            status: $data['status'] ?? 'pending',
        );
    }

    public function handleCallback(Request $request): PaymentStatus
    {
        $paymentId = $request->input('payment_id');

        // Проверяем статус платежа
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
        ])->get("{$this->baseUrl}/v1/payments/{$paymentId}");

        if (!$response->successful()) {
            Log::error('WebPay payment status retrieval failed', [
                'payment_id' => $paymentId,
                'response' => $response->body(),
            ]);
            throw new \Exception('Failed to retrieve WebPay payment status');
        }

        $data = $response->json();

        $status = match ($data['status'] ?? null) {
            'completed', 'success', 'paid' => 'success',
            'pending', 'processing' => 'pending',
            'cancelled', 'canceled' => 'cancelled',
            default => 'failed',
        };

        return new PaymentStatus(
            status: $status,
            transactionId: $data['payment_id'] ?? $data['id'],
        );
    }
}

