<?php

namespace App\Modules\PaymentModule\Gateways;

use App\Modules\PaymentModule\Contracts\PaymentGateway;
use App\Modules\PaymentModule\Contracts\PaymentResponse;
use App\Modules\PaymentModule\Contracts\PaymentStatus;
use App\Modules\PaymentModule\Contracts\RefundResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class YooKassaGateway implements PaymentGateway
{
    private string $shopId;

    private string $secretKey;

    private string $baseUrl;

    public function __construct()
    {
        $this->shopId = config('services.yookassa.shop_id');
        $this->secretKey = config('services.yookassa.secret_key');
        $this->baseUrl = 'https://api.yookassa.ru/v3';
    }

    public function createPayment(
        float $amount,
        string $description,
        array $metadata = [],
        ?array $receipt = null,
        ?string $confirmationType = 'redirect',
        bool $capture = true
    ): PaymentResponse {
        // Генерируем уникальный ключ идемпотентности
        $idempotenceKey = Str::uuid()->toString();

        $payload = [
            'amount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => 'RUB',
            ],
            'capture' => $capture,
            'description' => $description,
            'metadata' => $metadata,
        ];

        // Добавляем подтверждение в зависимости от типа
        $payload['confirmation'] = $this->buildConfirmation($confirmationType);

        // Добавляем чек если передан (54-ФЗ)
        if ($receipt !== null) {
            $payload['receipt'] = $receipt;
        }

        $response = Http::withBasicAuth($this->shopId, $this->secretKey)
            ->withHeaders([
                'Idempotence-Key' => $idempotenceKey,
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}/payments", $payload);

        if (! $response->successful()) {
            Log::error('YooKassa payment creation failed', [
                'response' => $response->body(),
                'status' => $response->status(),
                'payload' => $payload,
            ]);
            throw new \Exception('Failed to create YooKassa payment: '.$response->body());
        }

        $data = $response->json();

        return new PaymentResponse(
            paymentId: $data['id'],
            paymentUrl: $data['confirmation']['confirmation_url'] ?? '',
            status: $data['status'],
            metadata: $data['metadata'] ?? null,
            confirmationType: $data['confirmation']['type'] ?? null,
        );
    }

    public function handleCallback(Request $request): PaymentStatus
    {
        $data = $request->all();

        // YooKassa отправляет уведомления в формате webhook
        $event = $data['event'] ?? null;
        $object = $data['object'] ?? [];

        $status = match ($event) {
            'payment.succeeded' => 'success',
            'payment.canceled' => 'cancelled',
            'payment.waiting_for_capture' => 'waiting_for_capture',
            'refund.succeeded' => 'refunded',
            default => 'pending',
        };

        return new PaymentStatus(
            status: $status,
            transactionId: $object['id'] ?? null,
            metadata: $object['metadata'] ?? null,
        );
    }

    public function getPaymentInfo(string $paymentId): ?array
    {
        $response = Http::withBasicAuth($this->shopId, $this->secretKey)
            ->get("{$this->baseUrl}/payments/{$paymentId}");

        if (! $response->successful()) {
            Log::error('YooKassa get payment info failed', [
                'payment_id' => $paymentId,
                'response' => $response->body(),
                'status' => $response->status(),
            ]);

            return null;
        }

        return $response->json();
    }

    public function capturePayment(string $paymentId, ?float $amount = null): PaymentResponse
    {
        $idempotenceKey = Str::uuid()->toString();

        $payload = [];

        if ($amount !== null) {
            $payload['amount'] = [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => 'RUB',
            ];
        }

        $response = Http::withBasicAuth($this->shopId, $this->secretKey)
            ->withHeaders([
                'Idempotence-Key' => $idempotenceKey,
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}/payments/{$paymentId}/capture", $payload);

        if (! $response->successful()) {
            Log::error('YooKassa payment capture failed', [
                'payment_id' => $paymentId,
                'response' => $response->body(),
                'status' => $response->status(),
            ]);
            throw new \Exception('Failed to capture YooKassa payment: '.$response->body());
        }

        $data = $response->json();

        return new PaymentResponse(
            paymentId: $data['id'],
            paymentUrl: '',
            status: $data['status'],
            metadata: $data['metadata'] ?? null,
        );
    }

    public function cancelPayment(string $paymentId): PaymentResponse
    {
        $idempotenceKey = Str::uuid()->toString();

        $response = Http::withBasicAuth($this->shopId, $this->secretKey)
            ->withHeaders([
                'Idempotence-Key' => $idempotenceKey,
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}/payments/{$paymentId}/cancel");

        if (! $response->successful()) {
            Log::error('YooKassa payment cancellation failed', [
                'payment_id' => $paymentId,
                'response' => $response->body(),
                'status' => $response->status(),
            ]);
            throw new \Exception('Failed to cancel YooKassa payment: '.$response->body());
        }

        $data = $response->json();

        return new PaymentResponse(
            paymentId: $data['id'],
            paymentUrl: '',
            status: $data['status'],
            metadata: $data['metadata'] ?? null,
        );
    }

    public function createRefund(string $paymentId, float $amount, ?array $receipt = null): RefundResponse
    {
        $idempotenceKey = Str::uuid()->toString();

        $payload = [
            'payment_id' => $paymentId,
            'amount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => 'RUB',
            ],
        ];

        // Добавляем чек для возврата если передан
        if ($receipt !== null) {
            $payload['receipt'] = $receipt;
        }

        $response = Http::withBasicAuth($this->shopId, $this->secretKey)
            ->withHeaders([
                'Idempotence-Key' => $idempotenceKey,
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}/refunds", $payload);

        if (! $response->successful()) {
            Log::error('YooKassa refund creation failed', [
                'payment_id' => $paymentId,
                'response' => $response->body(),
                'status' => $response->status(),
            ]);
            throw new \Exception('Failed to create YooKassa refund: '.$response->body());
        }

        $data = $response->json();

        return new RefundResponse(
            refundId: $data['id'],
            status: $data['status'],
            amount: (float) $data['amount']['value'],
            paymentId: $data['payment_id'],
            createdAt: $data['created_at'] ?? null,
        );
    }

    /**
     * Построить объект подтверждения в зависимости от типа
     */
    private function buildConfirmation(string $type): array
    {
        return match ($type) {
            'redirect' => [
                'type' => 'redirect',
                'return_url' => config('app.url').'/payment/callback',
            ],
            'embedded' => [
                'type' => 'embedded',
            ],
            'qr' => [
                'type' => 'qr',
            ],
            default => [
                'type' => 'redirect',
                'return_url' => config('app.url').'/payment/callback',
            ],
        };
    }
}
