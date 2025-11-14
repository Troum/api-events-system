<?php

namespace App\Modules\PaymentModule\DTO;

class PaymentDto
{
    public function __construct(
        public readonly int $bookingId,
        public readonly float $amount,
        public readonly string $provider,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            bookingId: $data['booking_id'],
            amount: (float) $data['amount'],
            provider: $data['provider'],
        );
    }
}
