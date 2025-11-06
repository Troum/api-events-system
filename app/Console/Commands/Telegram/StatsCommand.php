<?php

namespace App\Console\Commands\Telegram;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class StatsCommand extends Command
{
    protected $signature = 'telegram:stats';
    protected $description = 'Send booking statistics to Telegram';

    public function handle(): int
    {
        $totalBookings = Booking::count();
        $paidBookings = Booking::where('payment_status', 'paid')->count();
        $pendingBookings = Booking::where('payment_status', 'pending')->count();
        $totalRevenue = Booking::where('payment_status', 'paid')
            ->with('trip')
            ->get()
            ->sum(fn($booking) => $booking->trip->price * $booking->seats);

        $message = "📊 <b>Статистика бронирований</b>\n\n";
        $message .= "Всего бронирований: {$totalBookings}\n";
        $message .= "Оплачено: {$paidBookings}\n";
        $message .= "Ожидают оплаты: {$pendingBookings}\n";
        $message .= "Общая выручка: " . number_format($totalRevenue, 2, '.', ' ') . " ₽";

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
