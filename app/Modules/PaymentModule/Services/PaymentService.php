<?php

namespace App\Modules\PaymentModule\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Modules\PaymentModule\Contracts\PaymentGateway;
use App\Modules\PaymentModule\DTO\PaymentDto;
use App\Modules\PaymentModule\Gateways\YooKassaGateway;
use App\Modules\PaymentModule\Gateways\StripeGateway;
use App\Modules\PaymentModule\Gateways\PayPalGateway;
use App\Modules\PaymentModule\Gateways\WebPayGateway;
use Illuminate\Http\Request;

class PaymentService
{
    public function createPayment(PaymentDto $dto): Payment
    {
        $booking = Booking::with('trip')->findOrFail($dto->bookingId);

        $gateway = $this->getGateway($dto->provider);

        $response = $gateway->createPayment(
            amount: $dto->amount,
            description: "Оплата бронирования #{$booking->id}",
            metadata: [
                'booking_id' => $booking->id,
            ]
        );

        $payment = Payment::create([
            'booking_id' => $dto->bookingId,
            'amount' => $dto->amount,
            'provider' => $dto->provider,
            'status' => 'pending',
            'transaction_id' => $response->paymentId,
        ]);

        // Сохраняем payment_url в кеше для временного хранения
        cache()->put("payment_url_{$payment->id}", $response->paymentUrl, now()->addHours(24));

        return $payment;
    }

    public function handleCallback(string $provider, Request $request): Payment
    {
        $gateway = $this->getGateway($provider);
        $status = $gateway->handleCallback($request);

        $payment = Payment::where('transaction_id', $status->transactionId)
            ->orWhere('transaction_id', $request->input('object.id'))
            ->firstOrFail();

        $payment->update([
            'status' => $status->status,
            'transaction_id' => $status->transactionId ?? $payment->transaction_id,
        ]);

        // Обновляем статус бронирования
        if ($status->status === 'success') {
            $payment->booking->update(['payment_status' => 'paid']);
        }

        return $payment->fresh();
    }

    public function getPaymentUrl(int $paymentId): ?string
    {
        return cache()->get("payment_url_{$paymentId}");
    }

    private function getGateway(string $provider): PaymentGateway
    {
        return match ($provider) {
            'yookassa' => new YooKassaGateway(),
            'stripe' => new StripeGateway(),
            'paypal' => new PayPalGateway(),
            'webpay' => new WebPayGateway(),
            default => throw new \InvalidArgumentException("Unknown payment provider: {$provider}"),
        };
    }
}

