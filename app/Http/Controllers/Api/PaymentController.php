<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\PaymentModule\DTO\PaymentDto;
use App\Modules\PaymentModule\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'provider' => 'required|in:yookassa,fondy',
        ]);

        $booking = \App\Models\Booking::with('trip')->findOrFail($validated['booking_id']);
        $amount = $booking->trip->price * $booking->seats;

        $dto = new PaymentDto(
            bookingId: $validated['booking_id'],
            amount: $amount,
            provider: $validated['provider']
        );

        $payment = $this->paymentService->createPayment($dto);
        $paymentUrl = $this->paymentService->getPaymentUrl($payment->id);

        return response()->json([
            'data' => $payment,
            'payment_url' => $paymentUrl,
        ], 201);
    }

    public function handleYooKassaCallback(Request $request): JsonResponse
    {
        $payment = $this->paymentService->handleCallback('yookassa', $request);
        return response()->json(['data' => $payment]);
    }

    public function handleFondyCallback(Request $request): JsonResponse
    {
        $payment = $this->paymentService->handleCallback('fondy', $request);
        return response()->json(['data' => $payment]);
    }
}
