<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'amount',
        'provider',
        'status',
        'transaction_id',
        'metadata',
        'confirmation_type',
        'refund_id',
        'refunded_amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Проверить, был ли платеж полностью возвращен
     */
    public function isFullyRefunded(): bool
    {
        return $this->refunded_amount >= $this->amount;
    }

    /**
     * Проверить, был ли платеж частично возвращен
     */
    public function isPartiallyRefunded(): bool
    {
        return $this->refunded_amount > 0 && $this->refunded_amount < $this->amount;
    }

    /**
     * Получить сумму, доступную для возврата
     */
    public function getRefundableAmount(): float
    {
        return (float) ($this->amount - $this->refunded_amount);
    }
}
