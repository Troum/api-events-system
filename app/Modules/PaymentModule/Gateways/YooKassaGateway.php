<?php

namespace App\Modules\PaymentModule\Gateways;

use App\Modules\PaymentModule\Contracts\PaymentGateway;
use App\Modules\PaymentModule\Contracts\PaymentResponse;
use App\Modules\PaymentModule\Contracts\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YooKassaGateway implements PaymentGateway
{
    private string $shopId;
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->shopId = config('services.yookassa.shop_id');
        $this->secretKey = config('services.yookassa.secret_key');
        $this->baseUrl = config('services.yookassa.test_mode', true) 
            ? 'https://api.yookassa.ru/v3' 
            : 'https://api.yookassa.ru/v3';
    }

    public function createPayment(float $amount, string $description, array $metadata = []): PaymentResponse
    {
        $response = Http::withBasicAuth($this->shopId, $this->secretKey)
            ->post("{$this->baseUrl}/payments", [
                'amount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency' => 'RUB',
                ],
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => config('app.url') . '/payment/callback',
                ],
                'description' => $description,
                'metadata' => $metadata,
            ]);

        if (!$response->successful()) {
            Log::error('YooKassa payment creation failed', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);
            throw new \Exception('Failed to create YooKassa payment');
        }

        $data = $response->json();

        return new PaymentResponse(
            paymentId: $data['id'],
            paymentUrl: $data['confirmation']['confirmation_url'] ?? '',
            status: $data['status'],
        );
    }

    public function handleCallback(Request $request): PaymentStatus
    {
        $data = $request->all();

        // YooKassa отправляет уведомления в формате webhook
        $status = match ($data['event'] ?? null) {
            'payment.succeeded' => 'success',
            'payment.canceled' => 'cancelled',
            default => 'failed',
        };

        return new PaymentStatus(
            status: $status,
            transactionId: $data['object']['id'] ?? null,
        );
    }
}

