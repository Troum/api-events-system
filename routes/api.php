<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\MapConfigController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TripController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Events используют slug вместо id
    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{event:slug}', [EventController::class, 'show'])->name('events.show');
    Route::post('events', [EventController::class, 'store']);
    Route::put('events/{event:slug}', [EventController::class, 'update'])->name('events.update');
    Route::patch('events/{event:slug}', [EventController::class, 'update'])->name('events.update');
    Route::delete('events/{event:slug}', [EventController::class, 'destroy'])->name('events.destroy');

    Route::apiResource('trips', TripController::class);
    Route::apiResource('bookings', BookingController::class)->only(['index', 'show', 'store']);

    Route::post('payments', [PaymentController::class, 'store']);
    Route::post('payments/yookassa/callback', [PaymentController::class, 'handleYooKassaCallback']);
    Route::post('payments/fondy/callback', [PaymentController::class, 'handleFondyCallback']);

    // Maps Configuration
    Route::get('maps/config', [MapConfigController::class, 'index']);
    Route::get('maps/provider', [MapConfigController::class, 'checkProvider']);
    Route::get('maps/geo-info', [MapConfigController::class, 'geoInfo']);

    // Auth & Account
    Route::post('auth/magic-link', [AuthController::class, 'sendMagicLink']);
    Route::post('auth/login', [AuthController::class, 'loginWithToken']);
    Route::get('account/bookings', [AuthController::class, 'myBookings']);
    Route::post('account/bookings/{id}/cancel', [AuthController::class, 'cancelBooking']);
    Route::post('account/bookings/{id}/refund', [AuthController::class, 'requestRefund']);
});
