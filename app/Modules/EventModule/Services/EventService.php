<?php

namespace App\Modules\EventModule\Services;

use App\Models\Event;
use App\Modules\EventModule\DTO\EventDto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EventService
{
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Event::query()->with('trips');

        if (isset($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%')
                ->orWhere('description', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('date_start', 'desc')->paginate($perPage);
    }

    public function getById(int $id): Event
    {
        return Event::with('trips')->findOrFail($id);
    }

    public function create(EventDto $dto): Event
    {
        return Event::create([
            'title' => $dto->title,
            'description' => $dto->description,
            'image' => $dto->image,
            'date_start' => $dto->dateStart,
            'date_end' => $dto->dateEnd,
            'location' => $dto->location,
        ]);
    }

    public function update(int $id, EventDto $dto): Event
    {
        $event = Event::findOrFail($id);
        $event->update([
            'title' => $dto->title,
            'description' => $dto->description,
            'image' => $dto->image,
            'date_start' => $dto->dateStart,
            'date_end' => $dto->dateEnd,
            'location' => $dto->location,
        ]);

        return $event->fresh();
    }

    public function delete(int $id): bool
    {
        return Event::findOrFail($id)->delete();
    }
}

