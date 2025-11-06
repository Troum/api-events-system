<?php

namespace App\Models;

use App\Enums\PaymentGatewayEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'trip_id',
        'user_name',
        'user_phone',
        'user_email',
        'seats',
        'payment_status',
        'payment_gateway',
        'status',
        'cancelled_at',
        'cancellation_reason',
        'refund_requested_at',
        'refunded_at',
        'refund_amount',
    ];

    protected $casts = [
        'payment_status' => 'string',
        'payment_gateway' => PaymentGatewayEnum::class,
        'cancelled_at' => 'datetime',
        'refund_requested_at' => 'datetime',
        'refunded_at' => 'datetime',
        'refund_amount' => 'decimal:2',
    ];

    /**
     * Проверить, можно ли отменить бронирование
     */
    public function canBeCancelled(): bool
    {
        return !in_array($this->status, ['cancelled', 'refunded']);
    }

    /**
     * Проверить, можно ли запросить возврат средств
     */
    public function canRequestRefund(): bool
    {
        return $this->payment_gateway?->value !== 'pay_on_arrival'
            && $this->payment_status === 'paid'
            && !in_array($this->status, ['refund_requested', 'refunded', 'cancelled']);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
