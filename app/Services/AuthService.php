<?php

namespace App\Services;

use App\Models\LoginToken;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\LoginTokenRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function __construct(
        private LoginTokenRepositoryInterface $loginTokenRepository,
        private BookingRepositoryInterface $bookingRepository
    ) {}

    /**
     * Отправить magic link на email
     */
    public function sendMagicLink(string $email): array
    {
        // Проверяем, есть ли бронирования с этим email
        $bookingsCount = $this->bookingRepository->countByEmail($email);

        if ($bookingsCount === 0) {
            return [
                'success' => false,
                'message' => 'Бронирований с данным email не найдено'
            ];
        }

        // Создаем токен
        $loginToken = $this->loginTokenRepository->createForEmail($email);

        // Логируем для разработки (потом заменим на отправку email)
        $loginUrl = config('app.frontend_url') . '/account/login?token=' . $loginToken->token;
        
        Log::info('Magic link created', [
            'email' => $email,
            'token' => $loginToken->token,
            'url' => $loginUrl
        ]);

        // TODO: Отправка email
        // $this->mailService->sendMagicLink($email, $loginUrl);

        return [
            'success' => true,
            'message' => 'Ссылка для входа отправлена на ваш email',
            'debug_token' => app()->environment('local') ? $loginToken->token : null,
        ];
    }

    /**
     * Войти по токену
     */
    public function loginWithToken(string $tokenString): array
    {
        $loginToken = $this->loginTokenRepository->findByToken($tokenString);

        if (!$loginToken) {
            return [
                'success' => false,
                'message' => 'Неверная или устаревшая ссылка'
            ];
        }

        if (!$this->loginTokenRepository->isTokenValid($loginToken)) {
            return [
                'success' => false,
                'message' => 'Ссылка истекла или уже использована'
            ];
        }

        // Отмечаем токен как использованный
        $this->loginTokenRepository->markAsUsed($loginToken);

        // Получаем бронирования пользователя
        $bookings = $this->bookingRepository->getByUserEmail($loginToken->email, ['trip.event']);

        return [
            'success' => true,
            'email' => $loginToken->email,
            'token' => $loginToken->token,
            'bookings' => $bookings,
        ];
    }

    /**
     * Получить бронирования по токену
     */
    public function getBookingsByToken(string $tokenString): ?Collection
    {
        $loginToken = $this->loginTokenRepository->findByToken($tokenString);

        if (!$loginToken || !$loginToken->used_at) {
            return null;
        }

        return $this->bookingRepository->getByUserEmail($loginToken->email, ['trip.event']);
    }

    /**
     * Проверить, принадлежит ли бронирование пользователю с токеном
     */
    public function verifyBookingOwnership(string $tokenString, int $bookingId): ?object
    {
        $loginToken = $this->loginTokenRepository->findByToken($tokenString);

        if (!$loginToken || !$loginToken->used_at) {
            return null;
        }

        $booking = $this->bookingRepository->find($bookingId, ['*'], ['trip.event']);

        if (!$booking || $booking->user_email !== $loginToken->email) {
            return null;
        }

        return $booking;
    }
}

