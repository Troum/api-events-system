<?php

namespace App\Repositories;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EventRepository extends BaseRepository implements EventRepositoryInterface
{
    public function __construct(Event $model)
    {
        $this->model = $model;
    }

    public function getWithTrips(): Collection
    {
        return $this->model->with('trips')->get();
    }

    public function getWithTeam(): Collection
    {
        return $this->model->with('teamMembers')->get();
    }

    public function getWithPackages(): Collection
    {
        return $this->model->with('eventPackages')->get();
    }

    public function findBySlug(string $slug): ?Event
    {
        return $this->model
            ->with(['trips', 'teamMembers', 'eventPackages'])
            ->where('slug', $slug)
            ->first();
    }

    public function getUpcoming(int $limit = null): Collection
    {
        $query = $this->model
            ->where('date_start', '>=', now())
            ->orderBy('date_start');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getPast(int $limit = null): Collection
    {
        $query = $this->model
            ->where('date_end', '<', now())
            ->orderBy('date_end', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }
}

