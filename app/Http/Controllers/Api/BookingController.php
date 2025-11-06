<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingStatusRequest;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // Фильтрация по trip_id
        if ($request->has('trip_id')) {
            $bookings = $this->bookingService->getByTripId($request->input('trip_id'));
            return response()->json(['data' => $bookings]);
        }

        // Фильтрация по статусу
        if ($request->has('status')) {
            $bookings = $this->bookingService->getByStatus($request->input('status'));
            return response()->json(['data' => $bookings]);
        }

        // Все бронирования с пагинацией
        $bookings = $this->bookingService->getPaginated(
            perPage: $request->input('per_page', 15),
            withRelations: ['trip', 'trip.event']
        );

        return response()->json($bookings);
    }

    public function show(int $id): JsonResponse
    {
        $booking = $this->bookingService->getById($id, ['trip', 'trip.event']);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return response()->json(['data' => $booking]);
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->bookingService->create($request->validated());

        if (!$booking) {
            return response()->json([
                'message' => 'Not enough available seats'
            ], 422);
        }

        return response()->json(['data' => $booking], 201);
    }

    public function updateStatus(UpdateBookingStatusRequest $request, int $id): JsonResponse
    {
        $updated = $this->bookingService->updateStatus($id, $request->validated()['status']);

        if (!$updated) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return response()->json(['message' => 'Booking status updated successfully']);
    }

    public function confirm(int $id): JsonResponse
    {
        $confirmed = $this->bookingService->confirm($id);

        if (!$confirmed) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return response()->json(['message' => 'Booking confirmed successfully']);
    }

    public function cancel(int $id): JsonResponse
    {
        $cancelled = $this->bookingService->cancel($id);

        if (!$cancelled) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        return response()->json(['message' => 'Booking cancelled successfully']);
    }
}
