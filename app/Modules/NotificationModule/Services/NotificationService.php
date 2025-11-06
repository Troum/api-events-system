<?php

namespace App\Modules\NotificationModule\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendBookingCreated(Booking $booking): void
    {
        // Email уведомление
        try {
            Mail::send('emails.booking_created', ['booking' => $booking], function ($message) use ($booking) {
                $message->to($booking->user_email)
                    ->subject('Бронирование создано');
            });
        } catch (\Exception $e) {
            Log::error('Failed to send booking created email', ['error' => $e->getMessage()]);
        }

        // Telegram уведомление менеджеру
        $this->sendTelegramNotification(
            "Новое бронирование #{$booking->id}\n" .
            "Имя: {$booking->user_name}\n" .
            "Email: {$booking->user_email}\n" .
            "Мест: {$booking->seats}"
        );
    }

    public function sendBookingPaid(Booking $booking): void
    {
        // Email уведомление
        try {
            Mail::send('emails.booking_paid', ['booking' => $booking], function ($message) use ($booking) {
                $message->to($booking->user_email)
                    ->subject('Бронирование оплачено');
            });
        } catch (\Exception $e) {
            Log::error('Failed to send booking paid email', ['error' => $e->getMessage()]);
        }
    }

    private function sendTelegramNotification(string $message): void
    {
        $chatId = config('services.telegram.chat_id');
        $botToken = config('services.telegram.bot_token');

        if (!$chatId || !$botToken) {
            return;
        }

        try {
            \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram notification', ['error' => $e->getMessage()]);
        }
    }
}

