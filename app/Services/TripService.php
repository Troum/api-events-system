<?php

namespace App\Services;

use App\Models\Trip;
use App\Repositories\Contracts\TripRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TripService
{
    public function __construct(
        private TripRepositoryInterface $tripRepository
    ) {}

    /**
     * Get paginated trips
     */
    public function getPaginated(int $perPage = 15, array $withRelations = []): LengthAwarePaginator
    {
        return $this->tripRepository->paginate($perPage, ['*'], $withRelations);
    }

    /**
     * Get all trips
     */
    public function getAll(array $withRelations = []): Collection
    {
        return $this->tripRepository->all(['*'], $withRelations);
    }

    /**
     * Get trip by ID
     */
    public function getById(int $id, array $withRelations = []): ?Trip
    {
        return $this->tripRepository->find($id, ['*'], $withRelations);
    }

    /**
     * Get trips by event ID
     */
    public function getByEventId(int $eventId): Collection
    {
        return $this->tripRepository->getByEventId($eventId);
    }

    /**
     * Get available trips
     */
    public function getAvailable(): Collection
    {
        return $this->tripRepository->getAvailable();
    }

    /**
     * Get featured trips
     */
    public function getFeatured(): Collection
    {
        return $this->tripRepository->getFeatured();
    }

    /**
     * Create new trip
     */
    public function create(array $data): Trip
    {
        return $this->tripRepository->create($data);
    }

    /**
     * Update trip
     */
    public function update(int $id, array $data): bool
    {
        return $this->tripRepository->update($id, $data);
    }

    /**
     * Delete trip
     */
    public function delete(int $id): bool
    {
        return $this->tripRepository->delete($id);
    }

    /**
     * Increment seats taken
     */
    public function incrementSeatsTaken(int $tripId, int $count = 1): bool
    {
        return $this->tripRepository->incrementSeatsTaken($tripId, $count);
    }

    /**
     * Decrement seats taken
     */
    public function decrementSeatsTaken(int $tripId, int $count = 1): bool
    {
        return $this->tripRepository->decrementSeatsTaken($tripId, $count);
    }

    /**
     * Check if trip has available seats
     */
    public function hasAvailableSeats(int $tripId, int $requiredSeats = 1): bool
    {
        return $this->tripRepository->hasAvailableSeats($tripId, $requiredSeats);
    }
}

