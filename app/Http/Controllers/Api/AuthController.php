<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private BookingService $bookingService
    ) {}

    /**
     * Отправить magic link на email
     */
    public function sendMagicLink(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Некорректный email',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->authService->sendMagicLink($request->email);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 404);
        }

        return response()->json($result);
    }

    /**
     * Войти по токену
     */
    public function loginWithToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Токен не указан',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->authService->loginWithToken($request->token);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 401);
        }

        return response()->json($result);
    }

    /**
     * Получить мои бронирования
     */
    public function myBookings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Токен не указан',
                'errors' => $validator->errors()
            ], 422);
        }

        $bookings = $this->authService->getBookingsByToken($request->token);

        if ($bookings === null) {
            return response()->json([
                'message' => 'Неверный токен'
            ], 401);
        }

        return response()->json([
            'data' => $bookings
        ]);
    }

    /**
     * Отменить бронирование
     */
    public function cancelBooking(Request $request, int $bookingId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $booking = $this->authService->verifyBookingOwnership($request->token, $bookingId);

        if (!$booking) {
            return response()->json([
                'message' => 'Бронирование не найдено или у вас нет доступа'
            ], 404);
        }

        $cancelled = $this->bookingService->cancel($bookingId, $request->reason);

        if (!$cancelled) {
            return response()->json([
                'message' => 'Это бронирование нельзя отменить'
            ], 400);
        }

        return response()->json([
            'message' => 'Бронирование отменено',
            'data' => $this->bookingService->getById($bookingId, ['trip.event']),
        ]);
    }

    /**
     * Запросить возврат средств
     */
    public function requestRefund(Request $request, int $bookingId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $booking = $this->authService->verifyBookingOwnership($request->token, $bookingId);

        if (!$booking) {
            return response()->json([
                'message' => 'Бронирование не найдено или у вас нет доступа'
            ], 404);
        }

        $requested = $this->bookingService->requestRefund($bookingId);

        if (!$requested) {
            return response()->json([
                'message' => 'Для этого бронирования нельзя запросить возврат средств'
            ], 400);
        }

        // TODO: Отправить уведомление администратору

        return response()->json([
            'message' => 'Запрос на возврат средств отправлен. Мы свяжемся с вами в ближайшее время.',
            'data' => $this->bookingService->getById($bookingId, ['trip.event']),
        ]);
    }
}
