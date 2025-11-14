<?php

namespace App\Modules\PaymentModule\Contracts;

use Illuminate\Http\Request;

interface PaymentGateway
{
    public function createPayment(
        float $amount,
        string $description,
        array $metadata = [],
        ?array $receipt = null,
        ?string $confirmationType = 'redirect',
        bool $capture = true
    ): PaymentResponse;

    public function handleCallback(Request $request): PaymentStatus;

    public function getPaymentInfo(string $paymentId): ?array;

    public function capturePayment(string $paymentId, ?float $amount = null): PaymentResponse;

    public function cancelPayment(string $paymentId): PaymentResponse;

    public function createRefund(string $paymentId, float $amount, ?array $receipt = null): RefundResponse;
}
