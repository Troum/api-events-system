<?php

namespace App\Modules\PaymentModule\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Modules\PaymentModule\Contracts\PaymentGateway;
use App\Modules\PaymentModule\DTO\PaymentDto;
use App\Modules\PaymentModule\Gateways\PayPalGateway;
use App\Modules\PaymentModule\Gateways\StripeGateway;
use App\Modules\PaymentModule\Gateways\WebPayGateway;
use App\Modules\PaymentModule\Gateways\YooKassaGateway;
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
            ->with('booking')
            ->firstOrFail();

        $oldPaymentStatus = $payment->status;

        $payment->update([
            'status' => $status->status,
            'transaction_id' => $status->transactionId ?? $payment->transaction_id,
        ]);

        // Обновляем статус оплаты бронирования при успешной оплате
        if ($status->status === 'success' && $oldPaymentStatus !== 'success') {
            $booking = $payment->booking;
            $booking->update(['payment_status' => 'paid']);

            // Логируем успешную оплату
            \Illuminate\Support\Facades\Log::info('Payment successful for booking', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'provider' => $provider,
                'amount' => $payment->amount,
            ]);
        }

        // Обновляем статус оплаты при неудачной оплате
        if ($status->status === 'failed' && $oldPaymentStatus !== 'failed') {
            $booking = $payment->booking;
            $booking->update(['payment_status' => 'failed']);

            \Illuminate\Support\Facades\Log::warning('Payment failed for booking', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'provider' => $provider,
            ]);
        }

        return $payment->fresh(['booking']);
    }

    public function getPaymentUrl(int $paymentId): ?string
    {
        return cache()->get("payment_url_{$paymentId}");
    }

    /**
     * Создать возврат средств для бронирования
     */
    public function createRefundForBooking(Booking $booking, ?float $amount = null): ?\App\Modules\PaymentModule\Contracts\RefundResponse
    {
        // Проверяем, что бронирование оплачено и не через оплату при встрече
        if ($booking->payment_status !== 'paid' || $booking->payment_gateway?->value === 'pay_on_arrival') {
            return null;
        }

        // Находим успешный платеж для этого бронирования
        $payment = Payment::where('booking_id', $booking->id)
            ->where('status', 'success')
            ->where('provider', $booking->payment_gateway->value)
            ->first();

        if (! $payment || ! $payment->transaction_id) {
            return null;
        }

        // Определяем сумму возврата (полная сумма или указанная)
        $refundAmount = $amount ?? $payment->getRefundableAmount();

        if ($refundAmount <= 0) {
            return null;
        }

        try {
            $gateway = $this->getGateway($payment->provider);
            $refundResponse = $gateway->createRefund($payment->transaction_id, $refundAmount);

            // Обновляем информацию о возврате в платеже
            $payment->update([
                'refund_id' => $refundResponse->refundId,
                'refunded_amount' => ($payment->refunded_amount ?? 0) + $refundAmount,
            ]);

            // Обновляем статус бронирования на "возвращено"
            $booking->refresh();
            $booking->update([
                'status' => 'refunded',
                'refunded_at' => now(),
                'refund_amount' => ($booking->refund_amount ?? 0) + $refundAmount,
            ]);

            // Отправляем уведомление о возврате
            \Illuminate\Support\Facades\Mail::to($booking->user_email)->send(new \App\Mail\BookingRefunded($booking));

            return $refundResponse;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create refund for booking', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function getGateway(string $provider): PaymentGateway
    {
        return match ($provider) {
            'yookassa' => new YooKassaGateway,
            'stripe' => new StripeGateway,
            'paypal' => new PayPalGateway,
            'webpay' => new WebPayGateway,
            default => throw new \InvalidArgumentException("Unknown payment provider: {$provider}"),
        };
    }
}
