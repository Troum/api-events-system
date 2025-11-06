<?php

namespace App\Repositories\Contracts;

use App\Models\EventPackage;
use Illuminate\Database\Eloquent\Collection;

interface EventPackageRepositoryInterface extends RepositoryInterface
{
    /**
     * Get packages by event ID
     */
    public function getByEventId(int $eventId): Collection;

    /**
     * Get active packages
     */
    public function getActive(): Collection;

    /**
     * Get featured packages
     */
    public function getFeatured(): Collection;

    /**
     * Get packages with available seats
     */
    public function getAvailable(): Collection;

    /**
     * Increment participants count
     */
    public function incrementParticipants(int $packageId, int $count = 1): bool;

    /**
     * Decrement participants count
     */
    public function decrementParticipants(int $packageId, int $count = 1): bool;
}

