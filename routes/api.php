<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TripController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('events', EventController::class);
    Route::apiResource('trips', TripController::class);
    Route::apiResource('bookings', BookingController::class)->only(['index', 'show', 'store']);
    
    Route::post('payments', [PaymentController::class, 'store']);
    Route::post('payments/yookassa/callback', [PaymentController::class, 'handleYooKassaCallback']);
    Route::post('payments/fondy/callback', [PaymentController::class, 'handleFondyCallback']);
    
    // Auth & Account
    Route::post('auth/magic-link', [AuthController::class, 'sendMagicLink']);
    Route::post('auth/login', [AuthController::class, 'loginWithToken']);
    Route::get('account/bookings', [AuthController::class, 'myBookings']);
    Route::post('account/bookings/{id}/cancel', [AuthController::class, 'cancelBooking']);
    Route::post('account/bookings/{id}/refund', [AuthController::class, 'requestRefund']);
});

