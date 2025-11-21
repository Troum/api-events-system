<?php

namespace App\Modules\PaymentModule\Gateways;

use App\Modules\PaymentModule\Contracts\PaymentGateway;
use App\Modules\PaymentModule\Contracts\PaymentResponse;
use App\Modules\PaymentModule\Contracts\PaymentStatus;
use App\Modules\PaymentModule\Contracts\RefundResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    /**
     * Получить access token с кешированием
     * Согласно документации PayPal, токены действительны несколько часов
     */
    private function getAccessToken(): string
    {
        $cacheKey = 'paypal_access_token_'.md5($this->clientId);

        return Cache::remember($cacheKey, now()->addHours(8), function () {
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

            if (! $response->successful()) {
                $this->handlePayPalError($response, 'OAuth token retrieval');
        }

            $data = $response->json();

            return $data['access_token'];
        });
    }

    public function createPayment(
        float $amount,
        string $description,
        array $metadata = [],
        ?array $receipt = null,
        ?string $confirmationType = 'redirect',
        bool $capture = true
    ): PaymentResponse {
        $accessToken = $this->getAccessToken();

        // Генерируем уникальный request ID для идемпотентности
        $requestId = Str::uuid()->toString();

        $intent = $capture ? 'CAPTURE' : 'AUTHORIZE';

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'PayPal-Request-Id' => $requestId,
                'Prefer' => 'return=representation',
            ])
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => $intent,
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
                    'return_url' => config('app.url').'/payment/callback/paypal',
                    'cancel_url' => config('app.url').'/payment/cancel',
                    'brand_name' => config('app.name'),
                    'landing_page' => 'BILLING',
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        if (! $response->successful()) {
            $this->handlePayPalError($response, 'order creation');
        }

        $data = $response->json();

        // Находим ссылку для редиректа
        $approveUrl = collect($data['links'])->firstWhere('rel', 'approve')['href'] ?? '';

        return new PaymentResponse(
            paymentId: $data['id'],
            paymentUrl: $approveUrl,
            status: strtolower($data['status']),
            metadata: $metadata,
        );
    }

    public function getPaymentInfo(string $paymentId): ?array
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->get("{$this->baseUrl}/v2/checkout/orders/{$paymentId}");

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    public function capturePayment(string $paymentId, ?float $amount = null): PaymentResponse
    {
        $accessToken = $this->getAccessToken();

        // Генерируем request ID для идемпотентности
        $requestId = Str::uuid()->toString();

        $payload = [];
        if ($amount !== null) {
            $payload['amount'] = [
                'currency_code' => 'EUR',
                'value' => number_format($amount, 2, '.', ''),
            ];
        }

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'PayPal-Request-Id' => $requestId,
                'Prefer' => 'return=representation',
            ])
            ->post("{$this->baseUrl}/v2/checkout/orders/{$paymentId}/capture", $payload);

        if (! $response->successful()) {
            $this->handlePayPalError($response, 'payment capture');
        }

        $data = $response->json();

        return new PaymentResponse(
            paymentId: $data['id'],
            paymentUrl: '',
            status: strtolower($data['status']),
        );
    }

    public function cancelPayment(string $paymentId): PaymentResponse
    {
        throw new \Exception('PayPal orders expire automatically if not captured');
    }

    public function createRefund(string $paymentId, float $amount, ?array $receipt = null): RefundResponse
    {
        $accessToken = $this->getAccessToken();

        // Генерируем request ID для идемпотентности
        $requestId = Str::uuid()->toString();

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'PayPal-Request-Id' => $requestId,
                'Prefer' => 'return=representation',
            ])
            ->post("{$this->baseUrl}/v2/payments/captures/{$paymentId}/refund", [
                'amount' => [
                    'currency_code' => 'EUR',
                    'value' => number_format($amount, 2, '.', ''),
                ],
            ]);

        if (! $response->successful()) {
            $this->handlePayPalError($response, 'refund creation');
        }

        $data = $response->json();

        return new RefundResponse(
            refundId: $data['id'],
            status: strtolower($data['status']),
            amount: (float) $data['amount']['value'],
            paymentId: $paymentId,
            createdAt: $data['create_time'] ?? null,
        );
    }

    /**
     * Обработка ошибок PayPal API согласно документации
     */
    private function handlePayPalError($response, string $operation): void
    {
        $status = $response->status();
        $body = $response->json();

        $errorName = $body['name'] ?? 'UNKNOWN_ERROR';
        $errorMessage = $body['message'] ?? 'Unknown error';
        $errorDetails = $body['details'] ?? [];

        $logContext = [
            'operation' => $operation,
            'status' => $status,
            'error_name' => $errorName,
            'error_message' => $errorMessage,
            'error_details' => $errorDetails,
            'response' => $response->body(),
        ];

        // Логируем согласно типу ошибки
        match ($status) {
            400 => Log::warning("PayPal Bad Request during {$operation}", $logContext),
            401 => Log::error("PayPal Authentication Failed during {$operation}", $logContext),
            403 => Log::error("PayPal Forbidden during {$operation}", $logContext),
            404 => Log::warning("PayPal Resource Not Found during {$operation}", $logContext),
            409 => Log::warning("PayPal Conflict during {$operation}", $logContext),
            422 => Log::warning("PayPal Unprocessable Entity during {$operation}", $logContext),
            429 => Log::warning("PayPal Rate Limit Exceeded during {$operation}", $logContext),
            default => Log::error("PayPal Error during {$operation}", $logContext),
        };

        // Бросаем исключение с информативным сообщением
        throw new \Exception("PayPal {$operation} failed: {$errorMessage} (Name: {$errorName}, Status: {$status})");
    }

    public function handleCallback(Request $request): PaymentStatus
    {
        $orderId = $request->input('token');
        $accessToken = $this->getAccessToken();

        // Генерируем request ID для идемпотентности
        $requestId = Str::uuid()->toString();

        // Захватываем платеж
        $response = Http::withToken($accessToken)
            ->withHeaders([
                'PayPal-Request-Id' => $requestId,
                'Prefer' => 'return=representation',
            ])
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

        if (! $response->successful()) {
            $this->handlePayPalError($response, 'payment callback capture');
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
