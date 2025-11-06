<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $fillable = [
        'event_id',
        'title',
        'description',
        'images',
        'city_from',
        'city_to',
        'transport_type',
        'route_description',
        'departure_time',
        'arrival_time',
        'duration',
        'stops',
        'price',
        'seats_total',
        'seats_taken',
        'includes',
        'not_includes',
        'amenities',
        'luggage_allowance',
        'luggage_rules',
        'pickup_points',
        'dropoff_points',
        'driver_name',
        'driver_phone',
        'guide_name',
        'guide_phone',
        'additional_services',
        'cancellation_policy',
        'terms_and_conditions',
        'min_age',
        'requirements',
        'status',
        'is_featured',
        'allow_waitlist',
        'waitlist_count',
        'early_bird_price',
        'early_bird_deadline',
        'discounts',
        'rating',
        'reviews_count',
        'slug',
        'meta_description',
        'available_payment_gateways',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'price' => 'decimal:2',
        'early_bird_price' => 'decimal:2',
        'early_bird_deadline' => 'date',
        'rating' => 'decimal:2',
        'images' => 'array',
        'stops' => 'array',
        'includes' => 'array',
        'not_includes' => 'array',
        'amenities' => 'array',
        'pickup_points' => 'array',
        'dropoff_points' => 'array',
        'additional_services' => 'array',
        'discounts' => 'array',
        'available_payment_gateways' => 'array',
        'is_featured' => 'boolean',
        'allow_waitlist' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get available seats
     */
    public function getAvailableSeatsAttribute(): int
    {
        return $this->seats_total - $this->seats_taken;
    }

    /**
     * Check if trip is sold out
     */
    public function isSoldOut(): bool
    {
        return $this->available_seats <= 0;
    }

    /**
     * Check if early bird price is active
     */
    public function isEarlyBirdActive(): bool
    {
        if (!$this->early_bird_price || !$this->early_bird_deadline) {
            return false;
        }

        return now()->lte($this->early_bird_deadline);
    }

    /**
     * Get current price (early bird or regular)
     */
    public function getCurrentPriceAttribute(): float
    {
        if ($this->isEarlyBirdActive()) {
            return $this->early_bird_price;
        }

        return $this->price;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Check if trip is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if trip is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if trip is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get occupancy percentage
     */
    public function getOccupancyPercentageAttribute(): int
    {
        if ($this->seats_total === 0) {
            return 0;
        }

        return (int) (($this->seats_taken / $this->seats_total) * 100);
    }
}
