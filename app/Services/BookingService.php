<?php

namespace App\Services;

use App\Mail\BookingCancelled;
use App\Mail\BookingConfirmed;
use App\Mail\BookingCreated;
use App\Mail\BookingRefunded;
use App\Mail\BookingRefundRequested;
use App\Models\Booking;
use App\Modules\PaymentModule\Services\PaymentService;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\TripRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

readonly class BookingService
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository,
        private TripRepositoryInterface $tripRepository,
        private PaymentService $paymentService
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
        if (! $this->tripRepository->hasAvailableSeats($data['trip_id'], $data['seats'])) {
            return null;
        }

        // Создаем бронирование
        $booking = $this->bookingRepository->create($data);

        // Увеличиваем количество занятых мест
        $this->tripRepository->incrementSeatsTaken($data['trip_id'], $data['seats']);

        // Отправляем уведомление о создании бронирования
        $booking->load(['trip', 'trip.event']);
        Mail::to($booking->user_email)->send(new BookingCreated($booking));

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

        if (! $booking) {
            return false;
        }

        // Уменьшаем количество занятых мест
        $this->tripRepository->decrementSeatsTaken($booking->trip_id, $booking->seats);

        return $this->bookingRepository->delete($id);
    }

    /**
     * Update booking status
     */
    public function updateStatus(int $bookingId, string $status, ?string $reason = null): bool
    {
        $booking = $this->getById($bookingId, ['trip', 'trip.event', 'payments']);
        $oldStatus = $booking->status;

        // Если статус меняется на cancelled, используем метод cancel для обработки возврата
        if ($status === 'cancelled' && $oldStatus !== 'cancelled') {
            return $this->cancel($bookingId, $reason);
        }

        $updated = $this->bookingRepository->updateStatus($bookingId, $status);

        if ($updated && $oldStatus !== $status) {
            $booking->refresh();
            $booking->load(['trip', 'trip.event']);

            // Отправляем уведомление в зависимости от нового статуса
            match ($status) {
                'confirmed' => Mail::to($booking->user_email)->send(new BookingConfirmed($booking)),
                'refunded' => Mail::to($booking->user_email)->send(new BookingRefunded($booking)),
                default => null,
            };
        }

        return $updated;
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
        $booking = $this->getById($bookingId, ['trip', 'trip.event', 'payments']);

        if (! $booking) {
            return false;
        }

        if (! $booking->canBeCancelled()) {
            return false;
        }

        // Освобождаем места
        $this->tripRepository->decrementSeatsTaken($booking->trip_id, $booking->seats);

        // Если бронирование оплачено через платежную систему, ставим возврат в очередь
        $refundQueued = false;
        if ($booking->payment_status === 'paid' && $booking->payment_gateway?->value !== 'pay_on_arrival') {
            // Ставим возврат в очередь refund
            \App\Jobs\ProcessRefund::dispatch($bookingId)->onQueue('refund');
            $refundQueued = true;

            // Обновляем статус бронирования на "запрошен возврат"
            $updated = $this->bookingRepository->update($bookingId, [
                'status' => 'refund_requested',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'refund_requested_at' => now(),
            ]);
        } else {
            // Если оплата не была произведена или через оплату при встрече, просто отменяем
            $updated = $this->bookingRepository->update($bookingId, [
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);
        }

        if ($updated) {
            $booking->refresh();
            $booking->load(['trip', 'trip.event']);

            // Отправляем соответствующее уведомление
            if ($refundQueued) {
                Mail::to($booking->user_email)->send(new BookingRefundRequested($booking));
            } else {
                Mail::to($booking->user_email)->send(new BookingCancelled($booking));
            }
        }

        return $updated;
    }

    /**
     * Request refund
     */
    public function requestRefund(int $bookingId): bool
    {
        $booking = $this->getById($bookingId, ['trip', 'trip.event']);

        if (! $booking) {
            return false;
        }

        if (! $booking->canRequestRefund()) {
            return false;
        }

        $updated = $this->bookingRepository->update($bookingId, [
            'status' => 'refund_requested',
            'refund_requested_at' => now(),
        ]);

        if ($updated) {
            $booking->refresh();
            $booking->load(['trip', 'trip.event']);
            Mail::to($booking->user_email)->send(new BookingRefundRequested($booking));
        }

        return $updated;
    }

    /**
     * Process refund (admin action)
     */
    public function processRefund(int $bookingId, float $amount): bool
    {
        $booking = $this->getById($bookingId, ['trip', 'trip.event']);

        if (! $booking || $booking->status !== 'refund_requested') {
            return false;
        }

        $updated = $this->bookingRepository->update($bookingId, [
            'status' => 'refunded',
            'refunded_at' => now(),
            'refund_amount' => $amount,
        ]);

        if ($updated) {
            $booking->refresh();
            $booking->load(['trip', 'trip.event']);
            Mail::to($booking->user_email)->send(new BookingRefunded($booking));
        }

        return $updated;
    }
}
