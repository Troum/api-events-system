<?php

namespace App\Repositories;

use App\Models\Trip;
use App\Repositories\Contracts\TripRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TripRepository extends BaseRepository implements TripRepositoryInterface
{
    public function __construct(Trip $model)
    {
        $this->model = $model;
    }

    public function getByEventId(int $eventId): Collection
    {
        return $this->model
            ->where('event_id', $eventId)
            ->orderBy('departure_time')
            ->get();
    }

    public function getByEventSlug(string $eventSlug): Collection
    {
        return $this->model
            ->whereHas('event', function ($query) use ($eventSlug) {
                $query->where('slug', $eventSlug);
            })
            ->orderBy('departure_time')
            ->get();
    }

    public function getAvailable(): Collection
    {
        return $this->model
            ->whereRaw('seats_taken < seats_total')
            ->where('status', 'published')
            ->get();
    }

    public function getFeatured(): Collection
    {
        return $this->model
            ->where('is_featured', true)
            ->where('status', 'published')
            ->get();
    }

    public function incrementSeatsTaken(int $tripId, int $count = 1): bool
    {
        $trip = $this->find($tripId);

        if (! $trip) {
            return false;
        }

        // Проверяем доступность мест
        if ($trip->seats_taken + $count > $trip->seats_total) {
            return false;
        }

        $trip->increment('seats_taken', $count);

        return true;
    }

    public function decrementSeatsTaken(int $tripId, int $count = 1): bool
    {
        $trip = $this->find($tripId);

        if (! $trip) {
            return false;
        }

        $trip->decrement('seats_taken', $count);

        return true;
    }

    public function hasAvailableSeats(int $tripId, int $requiredSeats = 1): bool
    {
        $trip = $this->find($tripId);

        if (! $trip) {
            return false;
        }

        return ($trip->seats_total - $trip->seats_taken) >= $requiredSeats;
    }
}
