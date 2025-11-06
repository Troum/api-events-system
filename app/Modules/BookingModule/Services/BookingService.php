<?php

namespace App\Modules\BookingModule\Services;

use App\Models\Booking;
use App\Models\Trip;
use App\Modules\BookingModule\DTO\BookingDto;
use App\Modules\NotificationModule\Services\NotificationService;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingService
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Booking::query()->with(['trip.event']);

        if (isset($filters['trip_id'])) {
            $query->where('trip_id', $filters['trip_id']);
        }

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getById(int $id): Booking
    {
        return Booking::with(['trip.event', 'payments'])->findOrFail($id);
    }

    public function create(BookingDto $dto): Booking
    {
        $trip = Trip::findOrFail($dto->tripId);

        if ($trip->available_seats < $dto->seats) {
            throw new \Exception('Недостаточно мест');
        }

        $booking = Booking::create([
            'trip_id' => $dto->tripId,
            'user_name' => $dto->userName,
            'user_phone' => $dto->userPhone,
            'user_email' => $dto->userEmail,
            'seats' => $dto->seats,
            'payment_status' => 'pending',
        ]);

        $trip->increment('seats_taken', $dto->seats);

        // Отправка уведомлений
        $this->notificationService->sendBookingCreated($booking);

        return $booking->load(['trip.event']);
    }

    public function updatePaymentStatus(int $id, string $status): Booking
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['payment_status' => $status]);

        if ($status === 'paid') {
            $this->notificationService->sendBookingPaid($booking);
        }

        return $booking->fresh();
    }
}

