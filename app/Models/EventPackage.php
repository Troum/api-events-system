<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'max_participants',
        'current_participants',
        'includes',
        'not_includes',
        'is_active',
        'is_featured',
        'order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'includes' => 'array',
        'not_includes' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Событие, к которому относится пакет
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Бронирования этого пакета
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'package_id');
    }

    /**
     * Проверка доступности мест
     */
    public function hasAvailableSeats(): bool
    {
        if (!$this->max_participants) {
            return true;
        }

        return $this->current_participants < $this->max_participants;
    }

    /**
     * Получить количество доступных мест
     */
    public function getAvailableSeatsAttribute(): int
    {
        if (!$this->max_participants) {
            return PHP_INT_MAX;
        }

        return max(0, $this->max_participants - $this->current_participants);
    }
}
