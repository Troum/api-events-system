<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'role',
        'bio',
        'photo',
        'email',
        'phone',
        'rating',
        'social_links',
        'is_active',
    ];

    protected $casts = [
        'social_links' => 'array',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * События, в которых участвует член команды
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_team')
            ->withPivot('role_in_event', 'order')
            ->withTimestamps()
            ->orderByPivot('order');
    }
}
