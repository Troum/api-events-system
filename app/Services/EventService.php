<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EventService
{
    public function __construct(
        private EventRepositoryInterface $eventRepository
    ) {}

    /**
     * Get paginated events
     */
    public function getPaginated(int $perPage = 15, array $withRelations = []): LengthAwarePaginator
    {
        return $this->eventRepository->paginate($perPage, ['*'], $withRelations);
    }

    /**
     * Get all events
     */
    public function getAll(array $withRelations = []): Collection
    {
        return $this->eventRepository->all(['*'], $withRelations);
    }

    /**
     * Get event by ID
     */
    public function getById(int $id, array $withRelations = []): ?Event
    {
        return $this->eventRepository->find($id, ['*'], $withRelations);
    }

    /**
     * Get event by slug
     */
    public function getBySlug(string $slug): ?Event
    {
        return $this->eventRepository->findBySlug($slug);
    }

    /**
     * Get upcoming events
     */
    public function getUpcoming(int $limit = null): Collection
    {
        return $this->eventRepository->getUpcoming($limit);
    }

    /**
     * Get past events
     */
    public function getPast(int $limit = null): Collection
    {
        return $this->eventRepository->getPast($limit);
    }

    /**
     * Create new event
     */
    public function create(array $data): Event
    {
        // Генерируем slug если не указан
        if (!isset($data['slug']) && isset($data['title'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
        }

        return $this->eventRepository->create($data);
    }

    /**
     * Update event
     */
    public function update(int $id, array $data): bool
    {
        return $this->eventRepository->update($id, $data);
    }

    /**
     * Delete event
     */
    public function delete(int $id): bool
    {
        return $this->eventRepository->delete($id);
    }

    /**
     * Attach team member to event
     */
    public function attachTeamMember(int $eventId, int $teamMemberId, array $pivotData = []): void
    {
        $event = $this->eventRepository->findOrFail($eventId);
        $event->teamMembers()->attach($teamMemberId, $pivotData);
    }

    /**
     * Detach team member from event
     */
    public function detachTeamMember(int $eventId, int $teamMemberId): void
    {
        $event = $this->eventRepository->findOrFail($eventId);
        $event->teamMembers()->detach($teamMemberId);
    }

    /**
     * Sync team members for event
     */
    public function syncTeamMembers(int $eventId, array $teamMemberIds): void
    {
        $event = $this->eventRepository->findOrFail($eventId);
        $event->teamMembers()->sync($teamMemberIds);
    }

    /**
     * Get events with trips
     */
    public function getWithTrips(): Collection
    {
        return $this->eventRepository->getWithTrips();
    }

    /**
     * Get events with team
     */
    public function getWithTeam(): Collection
    {
        return $this->eventRepository->getWithTeam();
    }

    /**
     * Get events with packages
     */
    public function getWithPackages(): Collection
    {
        return $this->eventRepository->getWithPackages();
    }
}

