<?php

namespace App\Modules\PaymentModule\Contracts;

class PaymentResponse
{
    public function __construct(
        public readonly string $paymentId,
        public readonly string $paymentUrl,
        public readonly string $status,
    ) {
    }
}

class PaymentStatus
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $transactionId = null,
    ) {
    }
}

