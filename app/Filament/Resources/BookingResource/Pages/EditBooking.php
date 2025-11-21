<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Получаем оригинальные значения до сохранения
        $originalStatus = $this->record->getOriginal('status');
        $originalPaymentStatus = $this->record->getOriginal('payment_status');

        // Получаем новые значения после сохранения
        $newStatus = $this->record->status;
        $newPaymentStatus = $this->record->payment_status;

        $bookingService = app(\App\Services\BookingService::class);

        // Если статус бронирования изменился, обрабатываем через сервис (включая возврат средств)
        if ($originalStatus !== $newStatus) {
            // Если статус меняется на cancelled, используем cancel для обработки возврата
            if ($newStatus === 'cancelled' && $originalStatus !== 'cancelled') {
                // Получаем причину отмены из формы, если она была указана
                $reason = $this->record->cancellation_reason;
                // cancel уже обновляет статус, поэтому не нужно вызывать updateStatus
                $bookingService->cancel($this->record->id, $reason);
            } else {
                // Для других изменений статуса используем updateStatus
                // Передаем null для reason, так как это не отмена
                $bookingService->updateStatus($this->record->id, $newStatus, null);
            }
        }

        // Если статус оплаты изменился на 'paid', логируем это
        // (обычно это обрабатывается через webhook платежной системы)
        if ($originalPaymentStatus !== $newPaymentStatus && $newPaymentStatus === 'paid') {
            \Illuminate\Support\Facades\Log::info('Payment status manually updated to paid', [
                'booking_id' => $this->record->id,
                'old_status' => $originalPaymentStatus,
                'new_status' => $newPaymentStatus,
            ]);
        }
    }
}
