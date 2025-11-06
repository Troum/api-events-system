<?php

namespace App\Repositories\Contracts;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

interface BookingRepositoryInterface extends RepositoryInterface
{
    /**
     * Get bookings by trip ID
     */
    public function getByTripId(int $tripId): Collection;

    /**
     * Get bookings by user email
     */
    public function getByUserEmail(string $email, array $withRelations = []): Collection;

    /**
     * Count bookings by user email
     */
    public function countByEmail(string $email): int;

    /**
     * Get bookings by status
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get pending bookings
     */
    public function getPending(): Collection;

    /**
     * Get confirmed bookings
     */
    public function getConfirmed(): Collection;

    /**
     * Update booking status
     */
    public function updateStatus(int $bookingId, string $status): bool;
}

