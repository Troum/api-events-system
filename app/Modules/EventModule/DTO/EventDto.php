<?php

namespace App\Modules\EventModule\DTO;

class EventDto
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly ?string $image,
        public readonly \DateTime $dateStart,
        public readonly \DateTime $dateEnd,
        public readonly string $location,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'],
            image: $data['image'] ?? null,
            dateStart: new \DateTime($data['date_start']),
            dateEnd: new \DateTime($data['date_end']),
            location: $data['location'],
        );
    }
}

