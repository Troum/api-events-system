<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Modules\PaymentModule\Services\PaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessRefund implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'refund';

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $bookingId,
        public ?float $amount = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(PaymentService $paymentService): void
    {
        $booking = Booking::with(['trip', 'trip.event', 'payments'])->find($this->bookingId);

        if (! $booking) {
            Log::warning('ProcessRefund: Booking not found', [
                'booking_id' => $this->bookingId,
            ]);

            return;
        }

        try {
            $refundResponse = $paymentService->createRefundForBooking($booking, $this->amount);

            if ($refundResponse) {
                Log::info('ProcessRefund: Refund processed successfully', [
                    'booking_id' => $this->bookingId,
                    'refund_id' => $refundResponse->refundId,
                    'amount' => $refundResponse->amount,
                ]);
            } else {
                Log::warning('ProcessRefund: Refund could not be created', [
                    'booking_id' => $this->bookingId,
                    'payment_status' => $booking->payment_status,
                    'payment_gateway' => $booking->payment_gateway?->value,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('ProcessRefund: Failed to process refund', [
                'booking_id' => $this->bookingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }
}
