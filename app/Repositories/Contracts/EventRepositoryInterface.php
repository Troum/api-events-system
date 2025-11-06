<?php

namespace App\Repositories\Contracts;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;

interface EventRepositoryInterface extends RepositoryInterface
{
    /**
     * Get events with trips
     */
    public function getWithTrips(): Collection;

    /**
     * Get events with team members
     */
    public function getWithTeam(): Collection;

    /**
     * Get events with packages
     */
    public function getWithPackages(): Collection;

    /**
     * Find event by slug
     */
    public function findBySlug(string $slug): ?Event;

    /**
     * Get upcoming events
     */
    public function getUpcoming(int $limit = null): Collection;

    /**
     * Get past events
     */
    public function getPast(int $limit = null): Collection;
}

