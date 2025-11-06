<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'slug',
        'description',
        'hero_description',
        'image',
        'hero_images',
        'date_start',
        'date_end',
        'location',
        'about',
        'features',
        'schedule',
        'infrastructure',
        'team',
        'packages',
        'not_included',
        'venue_name',
        'venue_description',
        'venue_address',
        'venue_latitude',
        'venue_longitude',
        'airport_distance',
        'recommended_flights',
        'faq',
        'gallery',
        'organizer_name',
        'organizer_phone',
        'organizer_email',
        'organizer_telegram',
        'organizer_whatsapp',
        'show_booking_form',
        'show_countdown',
        'max_participants',
        'current_participants',
        'meta_description',
        'meta_keywords',
        'og_image',
    ];

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
        'hero_images' => 'array',
        'features' => 'array',
        'schedule' => 'array',
        'infrastructure' => 'array',
        'team' => 'array',
        'packages' => 'array',
        'not_included' => 'array',
        'recommended_flights' => 'array',
        'faq' => 'array',
        'gallery' => 'array',
        'meta_keywords' => 'array',
        'show_booking_form' => 'boolean',
        'show_countdown' => 'boolean',
        'venue_latitude' => 'decimal:8',
        'venue_longitude' => 'decimal:8',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * Члены команды события
     */
    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(TeamMember::class, 'event_team')
            ->withPivot('role_in_event', 'order')
            ->withTimestamps()
            ->orderByPivot('order');
    }

    /**
     * Пакеты события
     */
    public function eventPackages(): HasMany
    {
        return $this->hasMany(EventPackage::class)->orderBy('order');
    }
    
    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
    
    /**
     * Get available seats across all trips
     */
    public function getAvailableSeatsAttribute(): int
    {
        return $this->trips->sum(function ($trip) {
            return $trip->seats_total - $trip->seats_taken;
        });
    }
    
    /**
     * Check if event is sold out
     */
    public function isSoldOut(): bool
    {
        if ($this->max_participants) {
            return $this->current_participants >= $this->max_participants;
        }
        
        return $this->available_seats <= 0;
    }
    
    /**
     * Get days until event starts
     */
    public function getDaysUntilStartAttribute(): int
    {
        return now()->diffInDays($this->date_start, false);
    }
}
