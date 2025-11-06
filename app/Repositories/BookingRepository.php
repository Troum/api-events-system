<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository extends BaseRepository implements BookingRepositoryInterface
{
    public function __construct(Booking $model)
    {
        $this->model = $model;
    }

    public function getByTripId(int $tripId): Collection
    {
        return $this->model
            ->where('trip_id', $tripId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByUserEmail(string $email, array $withRelations = []): Collection
    {
        $query = $this->model->where('user_email', $email);

        if (!empty($withRelations)) {
            $query->with($withRelations);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function countByEmail(string $email): int
    {
        return $this->model->where('user_email', $email)->count();
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPending(): Collection
    {
        return $this->getByStatus('pending');
    }

    public function getConfirmed(): Collection
    {
        return $this->getByStatus('confirmed');
    }

    public function updateStatus(int $bookingId, string $status): bool
    {
        return $this->update($bookingId, ['status' => $status]);
    }
}

