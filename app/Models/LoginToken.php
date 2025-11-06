<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LoginToken extends Model
{
    protected $fillable = [
        'email',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Создать новый токен для email
     */
    public static function createForEmail(string $email): self
    {
        // Удаляем старые неиспользованные токены для этого email
        self::where('email', $email)
            ->whereNull('used_at')
            ->delete();

        return self::create([
            'email' => $email,
            'token' => Str::random(64),
            'expires_at' => now()->addHours(24),
        ]);
    }

    /**
     * Проверить, валиден ли токен
     */
    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    /**
     * Отметить токен как использованный
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }
}
