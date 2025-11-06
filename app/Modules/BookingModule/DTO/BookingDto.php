<?php

namespace App\Modules\BookingModule\DTO;

class BookingDto
{
    public function __construct(
        public readonly int $tripId,
        public readonly string $userName,
        public readonly string $userPhone,
        public readonly string $userEmail,
        public readonly int $seats,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            tripId: $data['trip_id'],
            userName: $data['user_name'],
            userPhone: $data['user_phone'],
            userEmail: $data['user_email'],
            seats: (int) $data['seats'],
        );
    }
}

