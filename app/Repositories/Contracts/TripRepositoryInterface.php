<?php

namespace App\Repositories\Contracts;

use App\Models\Trip;
use Illuminate\Database\Eloquent\Collection;

interface TripRepositoryInterface extends RepositoryInterface
{
    /**
     * Get trips by event ID
     */
    public function getByEventId(int $eventId): Collection;

    /**
     * Get trips with available seats
     */
    public function getAvailable(): Collection;

    /**
     * Get featured trips
     */
    public function getFeatured(): Collection;

    /**
     * Increment seats taken
     */
    public function incrementSeatsTaken(int $tripId, int $count = 1): bool;

    /**
     * Decrement seats taken
     */
    public function decrementSeatsTaken(int $tripId, int $count = 1): bool;

    /**
     * Check if trip has available seats
     */
    public function hasAvailableSeats(int $tripId, int $requiredSeats = 1): bool;
}

