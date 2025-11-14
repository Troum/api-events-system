<?php

namespace App\Modules\PaymentModule\Contracts;

class PaymentResponse
{
    public function __construct(
        public readonly string $paymentId,
        public readonly string $paymentUrl,
        public readonly string $status,
        public readonly ?array $metadata = null,
        public readonly ?string $confirmationType = null,
    ) {}
}

class PaymentStatus
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $transactionId = null,
        public readonly ?array $metadata = null,
    ) {}
}

class RefundResponse
{
    public function __construct(
        public readonly string $refundId,
        public readonly string $status,
        public readonly float $amount,
        public readonly string $paymentId,
        public readonly ?string $createdAt = null,
    ) {}
}
