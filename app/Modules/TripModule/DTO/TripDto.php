<?php

namespace App\Modules\TripModule\DTO;

class TripDto
{
    public function __construct(
        public readonly int $eventId,
        public readonly string $cityFrom,
        public readonly \DateTime $departureTime,
        public readonly float $price,
        public readonly int $seatsTotal,
        public readonly int $seatsTaken = 0,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            eventId: $data['event_id'],
            cityFrom: $data['city_from'],
            departureTime: new \DateTime($data['departure_time']),
            price: (float) $data['price'],
            seatsTotal: (int) $data['seats_total'],
            seatsTaken: (int) ($data['seats_taken'] ?? 0),
        );
    }
}

