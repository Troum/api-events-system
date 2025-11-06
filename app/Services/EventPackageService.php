<?php

namespace App\Services;

use App\Models\EventPackage;
use App\Repositories\Contracts\EventPackageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EventPackageService
{
    public function __construct(
        private EventPackageRepositoryInterface $eventPackageRepository
    ) {}

    /**
     * Get paginated packages
     */
    public function getPaginated(int $perPage = 15, array $withRelations = []): LengthAwarePaginator
    {
        return $this->eventPackageRepository->paginate($perPage, ['*'], $withRelations);
    }

    /**
     * Get all packages
     */
    public function getAll(array $withRelations = []): Collection
    {
        return $this->eventPackageRepository->all(['*'], $withRelations);
    }

    /**
     * Get package by ID
     */
    public function getById(int $id, array $withRelations = []): ?EventPackage
    {
        return $this->eventPackageRepository->find($id, ['*'], $withRelations);
    }

    /**
     * Get packages by event ID
     */
    public function getByEventId(int $eventId): Collection
    {
        return $this->eventPackageRepository->getByEventId($eventId);
    }

    /**
     * Get active packages
     */
    public function getActive(): Collection
    {
        return $this->eventPackageRepository->getActive();
    }

    /**
     * Get featured packages
     */
    public function getFeatured(): Collection
    {
        return $this->eventPackageRepository->getFeatured();
    }

    /**
     * Get available packages
     */
    public function getAvailable(): Collection
    {
        return $this->eventPackageRepository->getAvailable();
    }

    /**
     * Create new package
     */
    public function create(array $data): EventPackage
    {
        return $this->eventPackageRepository->create($data);
    }

    /**
     * Update package
     */
    public function update(int $id, array $data): bool
    {
        return $this->eventPackageRepository->update($id, $data);
    }

    /**
     * Delete package
     */
    public function delete(int $id): bool
    {
        return $this->eventPackageRepository->delete($id);
    }

    /**
     * Increment participants count
     */
    public function incrementParticipants(int $packageId, int $count = 1): bool
    {
        return $this->eventPackageRepository->incrementParticipants($packageId, $count);
    }

    /**
     * Decrement participants count
     */
    public function decrementParticipants(int $packageId, int $count = 1): bool
    {
        return $this->eventPackageRepository->decrementParticipants($packageId, $count);
    }

    /**
     * Check if package has available seats
     */
    public function hasAvailableSeats(int $packageId): bool
    {
        $package = $this->getById($packageId);
        
        if (!$package) {
            return false;
        }

        return $package->hasAvailableSeats();
    }
}

