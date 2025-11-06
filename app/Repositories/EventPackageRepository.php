<?php

namespace App\Repositories;

use App\Models\EventPackage;
use App\Repositories\Contracts\EventPackageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EventPackageRepository extends BaseRepository implements EventPackageRepositoryInterface
{
    public function __construct(EventPackage $model)
    {
        $this->model = $model;
    }

    public function getByEventId(int $eventId): Collection
    {
        return $this->model
            ->where('event_id', $eventId)
            ->orderBy('order')
            ->get();
    }

    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function getFeatured(): Collection
    {
        return $this->model
            ->where('is_featured', true)
            ->where('is_active', true)
            ->get();
    }

    public function getAvailable(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('max_participants')
                    ->orWhereRaw('current_participants < max_participants');
            })
            ->get();
    }

    public function incrementParticipants(int $packageId, int $count = 1): bool
    {
        $package = $this->find($packageId);
        
        if (!$package) {
            return false;
        }

        $package->increment('current_participants', $count);
        
        return true;
    }

    public function decrementParticipants(int $packageId, int $count = 1): bool
    {
        $package = $this->find($packageId);
        
        if (!$package) {
            return false;
        }

        $package->decrement('current_participants', $count);
        
        return true;
    }
}

