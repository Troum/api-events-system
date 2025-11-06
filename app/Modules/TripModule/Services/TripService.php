<?php

namespace App\Modules\TripModule\Services;

use App\Models\Trip;
use App\Modules\TripModule\DTO\TripDto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TripService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Trip::query()->with('event');

        if (isset($filters['event_id'])) {
            $query->where('event_id', $filters['event_id']);
        }

        return $query->orderBy('departure_time', 'asc')->paginate($perPage);
    }

    public function getById(int $id): Trip
    {
        return Trip::with('event')->findOrFail($id);
    }

    public function create(TripDto $dto): Trip
    {
        return Trip::create([
            'event_id' => $dto->eventId,
            'city_from' => $dto->cityFrom,
            'departure_time' => $dto->departureTime,
            'price' => $dto->price,
            'seats_total' => $dto->seatsTotal,
            'seats_taken' => $dto->seatsTaken,
        ]);
    }

    public function update(int $id, TripDto $dto): Trip
    {
        $trip = Trip::findOrFail($id);
        $trip->update([
            'event_id' => $dto->eventId,
            'city_from' => $dto->cityFrom,
            'departure_time' => $dto->departureTime,
            'price' => $dto->price,
            'seats_total' => $dto->seatsTotal,
            'seats_taken' => $dto->seatsTaken,
        ]);

        return $trip->fresh();
    }

    public function delete(int $id): bool
    {
        return Trip::findOrFail($id)->delete();
    }
}

