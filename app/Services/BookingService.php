<?php

namespace App\Services;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\TripRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

readonly class BookingService
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository,
        private TripRepositoryInterface    $tripRepository
    ) {}

    /**
     * Get paginated bookings
     */
    public function getPaginated(int $perPage = 15, array $withRelations = []): LengthAwarePaginator
    {
        return $this->bookingRepository->paginate($perPage, ['*'], $withRelations);
    }

    /**
     * Get all bookings
     */
    public function getAll(array $withRelations = []): Collection
    {
        return $this->bookingRepository->all(['*'], $withRelations);
    }

    /**
     * Get booking by ID
     */
    public function getById(int $id, array $withRelations = []): Model
    {
        return $this->bookingRepository->find($id, ['*'], $withRelations);
    }

    /**
     * Get bookings by trip ID
     */
    public function getByTripId(int $tripId): Collection
    {
        return $this->bookingRepository->getByTripId($tripId);
    }

    /**
     * Get bookings by user email
     */
    public function getByUserEmail(string $email): Collection
    {
        return $this->bookingRepository->getByUserEmail($email);
    }

    /**
     * Get bookings by status
     */
    public function getByStatus(string $status): Collection
    {
        return $this->bookingRepository->getByStatus($status);
    }

    /**
     * Get pending bookings
     */
    public function getPending(): Collection
    {
        return $this->bookingRepository->getPending();
    }

    /**
     * Get confirmed bookings
     */
    public function getConfirmed(): Collection
    {
        return $this->bookingRepository->getConfirmed();
    }

    /**
     * Create new booking
     */
    public function create(array $data): ?Model
    {
        // Проверяем доступность мест
        if (!$this->tripRepository->hasAvailableSeats($data['trip_id'], $data['seats'])) {
            return null;
        }

        // Создаем бронирование
        $booking = $this->bookingRepository->create($data);

        // Увеличиваем количество занятых мест
        $this->tripRepository->incrementSeatsTaken($data['trip_id'], $data['seats']);

        return $booking;
    }

    /**
     * Update booking
     */
    public function update(int $id, array $data): bool
    {
        return $this->bookingRepository->update($id, $data);
    }

    /**
     * Delete booking
     */
    public function delete(int $id): bool
    {
        $booking = $this->getById($id);

        if (!$booking) {
            return false;
        }

        // Уменьшаем количество занятых мест
        $this->tripRepository->decrementSeatsTaken($booking->trip_id, $booking->seats);

        return $this->bookingRepository->delete($id);
    }

    /**
     * Update booking status
     */
    public function updateStatus(int $bookingId, string $status): bool
    {
        return $this->bookingRepository->updateStatus($bookingId, $status);
    }

    /**
     * Confirm booking
     */
    public function confirm(int $bookingId): bool
    {
        return $this->updateStatus($bookingId, 'confirmed');
    }

    /**
     * Cancel booking
     */
    public function cancel(int $bookingId, ?string $reason = null): bool
    {
        $booking = $this->getById($bookingId);

        if (!$booking) {
            return false;
        }

        if (!$booking->canBeCancelled()) {
            return false;
        }

        // Освобождаем места
        $this->tripRepository->decrementSeatsTaken($booking->trip_id, $booking->seats);

        return $this->bookingRepository->update($bookingId, [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Request refund
     */
    public function requestRefund(int $bookingId): bool
    {
        $booking = $this->getById($bookingId, ['trip']);

        if (!$booking) {
            return false;
        }

        if (!$booking->canRequestRefund()) {
            return false;
        }

        return $this->bookingRepository->update($bookingId, [
            'status' => 'refund_requested',
            'refund_requested_at' => now(),
        ]);
    }

    /**
     * Process refund (admin action)
     */
    public function processRefund(int $bookingId, float $amount): bool
    {
        $booking = $this->getById($bookingId);

        if (!$booking || $booking->status !== 'refund_requested') {
            return false;
        }

        return $this->bookingRepository->update($bookingId, [
            'status' => 'refunded',
            'refunded_at' => now(),
            'refund_amount' => $amount,
        ]);
    }
}

