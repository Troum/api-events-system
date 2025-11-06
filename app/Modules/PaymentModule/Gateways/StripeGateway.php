<?php

namespace App\Modules\PaymentModule\Gateways;

use App\Modules\PaymentModule\Contracts\PaymentGateway;
use App\Modules\PaymentModule\Contracts\PaymentResponse;
use App\Modules\PaymentModule\Contracts\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StripeGateway implements PaymentGateway
{
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret_key');
        $this->baseUrl = 'https://api.stripe.com/v1';
    }

    public function createPayment(float $amount, string $description, array $metadata = []): PaymentResponse
    {
        // Stripe работает с центами
        $amountInCents = (int) ($amount * 100);

        $response = Http::withToken($this->secretKey)
            ->asForm()
            ->post("{$this->baseUrl}/checkout/sessions", [
                'payment_method_types[]' => 'card',
                'line_items[0][price_data][currency]' => 'eur',
                'line_items[0][price_data][product_data][name]' => $description,
                'line_items[0][price_data][unit_amount]' => $amountInCents,
                'line_items[0][quantity]' => 1,
                'mode' => 'payment',
                'success_url' => config('app.url') . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.url') . '/payment/cancel',
                'metadata' => $metadata,
            ]);

        if (!$response->successful()) {
            Log::error('Stripe payment creation failed', [
                'response' => $response->body(),
                'status' => $response->status(),
            ]);
            throw new \Exception('Failed to create Stripe payment');
        }

        $data = $response->json();

        return new PaymentResponse(
            paymentId: $data['id'],
            paymentUrl: $data['url'] ?? '',
            status: $data['payment_status'] ?? 'pending',
        );
    }

    public function handleCallback(Request $request): PaymentStatus
    {
        $sessionId = $request->input('session_id');

        // Получаем информацию о сессии
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/checkout/sessions/{$sessionId}");

        if (!$response->successful()) {
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

