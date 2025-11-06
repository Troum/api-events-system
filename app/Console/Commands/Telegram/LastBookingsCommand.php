<?php

namespace App\Console\Commands\Telegram;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class LastBookingsCommand extends Command
{
    protected $signature = 'telegram:last {limit=5 : Number of last bookings to show}';
    protected $description = 'Send last bookings to Telegram';

    public function handle(): int
    {
        $limit = (int) $this->argument('limit');
        $bookings = Booking::with(['trip.event'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        if ($bookings->isEmpty()) {
            $message = "📋 Нет бронирований";
        } else {
            $message = "📋 <b>Последние {$limit} бронирований</b>\n\n";
            foreach ($bookings as $booking) {
                $status = match($booking->payment_status) {
                    'paid' => '✅ Оплачено',
                    'pending' => '⏳ Ожидает оплаты',
                    'failed' => '❌ Ошибка',
                    'cancelled' => '🚫 Отменено',
                    default => $booking->payment_status,
                };

                $message .= "🔹 <b>Бронирование #{$booking->id}</b>\n";
                $message .= "Мероприятие: {$booking->trip->event->title}\n";
                $message .= "Имя: {$booking->user_name}\n";
                $message .= "Мест: {$booking->seats}\n";
                $message .= "Статус: {$status}\n";
                $message .= "Дата: " . $booking->created_at->format('d.m.Y H:i') . "\n\n";
            }
        }

        $this->sendTelegramMessage($message);

        return Command::SUCCESS;
    }

    private function sendTelegramMessage(string $message): void
    {
        $chatId = config('services.telegram.chat_id');
        $botToken = config('services.telegram.bot_token');

        if (!$chatId || !$botToken) {
            $this->error('Telegram credentials not configured');
            return;
        }

        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);
    }
}
