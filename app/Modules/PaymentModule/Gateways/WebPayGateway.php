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

class WebPayGateway implements PaymentGateway
{
    private string $merchantId;

    private string $secretKey;

    private string $baseUrl;

    public function __construct()
    {
        $this->merchantId = config('services.webpay.merchant_id');
        $this->secretKey = config('services.webpay.secret_key');

        // Согласно документации WEBPAY
        $this->baseUrl = config('services.webpay.test_mode', true)
            ? 'https://sandbox.webpay.by'
            : 'https://billing.webpay.by';
    }

    public function createPayment(
        float $amount,
        string $description,
        array $metadata = [],
        ?array $receipt = null,
        ?string $confirmationType = 'redirect',
        bool $capture = true
    ): PaymentResponse {
        // WEBPAY работает с белорусскими рублями (BYN)
        // Сумма передается в копейках
        $amountInKopecks = (int) ($amount * 100);

        // Генерируем уникальный ID заказа
        $orderId = 'ORDER_'.Str::random(10).'_'.time();

        // Формируем данные для подписи согласно документации WEBPAY
        $signatureData = [
            'wsb_storeid' => $this->merchantId,
            'wsb_order_num' => $orderId,
            'wsb_currency_id' => 'BYN',
            'wsb_total' => number_format($amount, 2, '.', ''),
            'wsb_test' => config('services.webpay.test_mode', true) ? '1' : '0',
        ];

        // Генерируем HMAC подпись
        $signature = $this->generateSignature($signatureData);

        $payload = array_merge($signatureData, [
            'wsb_signature' => $signature,
            'wsb_return_url' => config('app.url').'/payment/callback/webpay',
            'wsb_cancel_return_url' => config('app.url').'/payment/cancel',
            'wsb_notify_url' => config('app.url').'/webhooks/webpay',
            'wsb_order_tag' => $description,
        ]);

        // Добавляем metadata
        if (! empty($metadata)) {
            foreach ($metadata as $key => $value) {
                $payload["wsb_custom_{$key}"] = $value;
            }
        }

        try {
            $response = Http::asForm()
                ->post("{$this->baseUrl}/api/payment", $payload);

            if (! $response->successful()) {
                $this->handleWebPayError($response, 'payment creation');
            }

            $data = $response->json();

            return new PaymentResponse(
                paymentId: $data['payment_id'] ?? $orderId,
                paymentUrl: $data['payment_url'] ?? "{$this->baseUrl}/payment",
                status: $data['status'] ?? 'pending',
                metadata: $metadata,
            );
        } catch (\Exception $e) {
            Log::error('WebPay payment creation failed', [
                'error' => $e->getMessage(),
                'merchant_id' => $this->merchantId,
            ]);
            throw $e;
        }
    }

    public function getPaymentInfo(string $paymentId): ?array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secretKey,
        ])->get("{$this->baseUrl}/v1/payments/{$paymentId}");

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    public function capturePayment(string $paymentId, ?float $amount = null): PaymentResponse
    {
        throw new \Exception('WebPay does not support manual capture');
    }

    public function cancelPayment(string $paymentId): PaymentResponse
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secretKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/v1/payments/{$paymentId}/cancel");

        if (! $response->successful()) {
            throw new \Exception('Failed to cancel WebPay payment: '.$response->body());
        }

        $data = $response->json();

        return new PaymentResponse(
            paymentId: $data['payment_id'] ?? $data['id'],
            paymentUrl: '',
            status: $data['status'] ?? 'cancelled',
        );
    }

    public function createRefund(string $paymentId, float $amount, ?array $receipt = null): RefundResponse
    {
        $amountInCents = (int) ($amount * 100);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secretKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/v1/refunds", [
            'payment_id' => $paymentId,
            'amount' => $amountInCents,
        ]);

        if (! $response->successful()) {
            throw new \Exception('Failed to create WebPay refund: '.$response->body());
        }

        $data = $response->json();

        return new RefundResponse(
            refundId: $data['refund_id'] ?? $data['id'],
            status: $data['status'],
            amount: ($data['amount'] ?? $amountInCents) / 100,
            paymentId: $paymentId,
            createdAt: $data['created_at'] ?? null,
        );
    }

    public function handleCallback(Request $request): PaymentStatus
    {
        // WEBPAY использует нотификатор (callback) для уведомлений
        $data = $request->all();

        // Проверяем подпись согласно документации WEBPAY
        $receivedSignature = $data['wsb_signature'] ?? '';
        unset($data['wsb_signature']);

        $calculatedSignature = $this->generateSignature($data);

        if ($receivedSignature !== $calculatedSignature) {
            Log::warning('WebPay callback signature mismatch', [
                'received' => $receivedSignature,
                'calculated' => $calculatedSignature,
            ]);
            throw new \Exception('Invalid signature');
        }

        // Определяем статус согласно документации WEBPAY
        $transactionType = $data['wsb_transaction_type'] ?? '';
        $responseCode = $data['rrn'] ?? $data['response_code'] ?? null;

        $status = match ($transactionType) {
            '1' => 'success',  // Успешная авторизация
            '2' => 'refunded', // Возврат
            '3' => 'cancelled', // Отмена
            default => $responseCode ? 'failed' : 'pending',
        };

        return new PaymentStatus(
            status: $status,
            transactionId: $data['wsb_order_num'] ?? $data['wsb_tid'] ?? null,
            metadata: [
                'transaction_type' => $transactionType,
                'amount' => $data['wsb_total'] ?? null,
                'currency' => $data['wsb_currency_id'] ?? 'BYN',
            ],
        );
    }

    /**
     * Генерация HMAC подписи согласно документации WEBPAY
     */
    private function generateSignature(array $data): string
    {
        // Сортируем параметры по ключу
        ksort($data);

        // Формируем строку для подписи
        $signatureString = '';
        foreach ($data as $key => $value) {
            // Пропускаем пустые значения и саму подпись
            if ($value !== '' && $value !== null && $key !== 'wsb_signature') {
                $signatureString .= $value;
            }
        }

        // Генерируем HMAC-SHA1 подпись с секретным ключом
        return hash_hmac('sha1', $signatureString, $this->secretKey);
    }

    /**
     * Обработка ошибок WEBPAY API
     */
    private function handleWebPayError($response, string $operation): void
    {
        $status = $response->status();
        $body = $response->json();

        $errorMessage = $body['message'] ?? $body['error'] ?? 'Unknown error';

        $logContext = [
            'operation' => $operation,
            'status' => $status,
            'error_message' => $errorMessage,
            'response' => $response->body(),
        ];

        match ($status) {
            400 => Log::warning("WEBPAY Bad Request during {$operation}", $logContext),
            401 => Log::error("WEBPAY Authentication Failed during {$operation}", $logContext),
            403 => Log::error("WEBPAY Forbidden during {$operation}", $logContext),
            404 => Log::warning("WEBPAY Resource Not Found during {$operation}", $logContext),
            422 => Log::warning("WEBPAY Validation Error during {$operation}", $logContext),
            429 => Log::warning("WEBPAY Rate Limit Exceeded during {$operation}", $logContext),
            default => Log::error("WEBPAY Error during {$operation}", $logContext),
        };

        throw new \Exception("WEBPAY {$operation} failed: {$errorMessage} (Status: {$status})");
    }
}
