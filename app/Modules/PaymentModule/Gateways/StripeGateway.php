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

class StripeGateway implements PaymentGateway
{
    private string $secretKey;

    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret_key');
        $this->baseUrl = 'https://api.stripe.com/v1';
    }

    public function createPayment(
        float $amount,
        string $description,
        array $metadata = [],
        ?array $receipt = null,
        ?string $confirmationType = 'redirect',
        bool $capture = true
    ): PaymentResponse {
        // Stripe работает с центами
        $amountInCents = (int) ($amount * 100);

        // Генерируем идемпотентный ключ для защиты от дублирования
        $idempotenceKey = Str::uuid()->toString();

        $response = Http::withToken($this->secretKey)
            ->withHeaders([
                'Idempotency-Key' => $idempotenceKey,
            ])
            ->asForm()
            ->post("{$this->baseUrl}/checkout/sessions", [
                'payment_method_types[]' => 'card',
                'line_items[0][price_data][currency]' => 'eur',
                'line_items[0][price_data][product_data][name]' => $description,
                'line_items[0][price_data][unit_amount]' => $amountInCents,
                'line_items[0][quantity]' => 1,
                'mode' => 'payment',
                'success_url' => config('app.url').'/payment/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.url').'/payment/cancel',
                'metadata' => $metadata,
            ]);

        if (! $response->successful()) {
            $this->handleStripeError($response, 'payment creation');
        }

        $data = $response->json();

        return new PaymentResponse(
            paymentId: $data['id'],
            paymentUrl: $data['url'] ?? '',
            status: $data['payment_status'] ?? 'pending',
            metadata: $metadata,
        );
    }

    public function getPaymentInfo(string $paymentId): ?array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/checkout/sessions/{$paymentId}");

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    public function capturePayment(string $paymentId, ?float $amount = null): PaymentResponse
    {
        throw new \Exception('Stripe does not support manual capture for checkout sessions');
    }

    public function cancelPayment(string $paymentId): PaymentResponse
    {
        throw new \Exception('Stripe does not support cancellation for checkout sessions');
    }

    public function createRefund(string $paymentId, float $amount, ?array $receipt = null): RefundResponse
    {
        $amountInCents = (int) ($amount * 100);

        // Генерируем идемпотентный ключ
        $idempotenceKey = Str::uuid()->toString();

        $response = Http::withToken($this->secretKey)
            ->withHeaders([
                'Idempotency-Key' => $idempotenceKey,
            ])
            ->asForm()
            ->post("{$this->baseUrl}/refunds", [
                'payment_intent' => $paymentId,
                'amount' => $amountInCents,
            ]);

        if (! $response->successful()) {
            $this->handleStripeError($response, 'refund creation');
        }

        $data = $response->json();

        return new RefundResponse(
            refundId: $data['id'],
            status: $data['status'],
            amount: $data['amount'] / 100,
            paymentId: $paymentId,
            createdAt: date('Y-m-d H:i:s', $data['created']),
        );
    }

    /**
     * Обработка ошибок Stripe API согласно документации
     */
    private function handleStripeError($response, string $operation): void
    {
        $status = $response->status();
        $body = $response->json();
        $errorType = $body['error']['type'] ?? 'unknown';
        $errorMessage = $body['error']['message'] ?? 'Unknown error';

        $logContext = [
            'operation' => $operation,
            'status' => $status,
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            'response' => $response->body(),
        ];

        // Логируем согласно типу ошибки
        match ($status) {
            400 => Log::warning("Stripe Bad Request during {$operation}", $logContext),
            401 => Log::error("Stripe Authentication Failed during {$operation}", $logContext),
            402 => Log::warning("Stripe Request Failed during {$operation}", $logContext),
            403 => Log::error("Stripe Forbidden during {$operation}", $logContext),
            404 => Log::warning("Stripe Resource Not Found during {$operation}", $logContext),
            409 => Log::warning("Stripe Idempotency Conflict during {$operation}", $logContext),
            429 => Log::warning("Stripe Rate Limit Exceeded during {$operation}", $logContext),
            default => Log::error("Stripe Error during {$operation}", $logContext),
        };

        // Бросаем исключение с информативным сообщением
        throw new \Exception("Stripe {$operation} failed: {$errorMessage} (Type: {$errorType}, Status: {$status})");
    }

    public function handleCallback(Request $request): PaymentStatus
    {
        $sessionId = $request->input('session_id');

        // Получаем информацию о сессии
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/checkout/sessions/{$sessionId}");

        if (! $response->successful()) {
            Log::error('Stripe session retrieval failed', [
                'session_id' => $sessionId,
                'response' => $response->body(),
            ]);
            throw new \Exception('Failed to retrieve Stripe session');
        }

        $data = $response->json();

        $status = match ($data['payment_status'] ?? null) {
            'paid' => 'success',
            'unpaid' => 'pending',
            default => 'failed',
        };

        return new PaymentStatus(
            status: $status,
            transactionId: $data['payment_intent'] ?? $sessionId,
        );
    }
}
