<?php

namespace App\Repositories;

use App\Models\LoginToken;
use App\Repositories\Contracts\LoginTokenRepositoryInterface;
use Illuminate\Support\Str;

class LoginTokenRepository extends BaseRepository implements LoginTokenRepositoryInterface
{
    public function __construct(LoginToken $model)
    {
        parent::__construct($model);
    }

    public function createForEmail(string $email): LoginToken
    {
        // Удаляем старые неиспользованные токены
        $this->deleteUnusedForEmail($email);

        return $this->create([
            'email' => $email,
            'token' => Str::random(64),
            'expires_at' => now()->addHours(24),
        ]);
    }

    public function findByToken(string $token): ?LoginToken
    {
        return $this->model->where('token', $token)->first();
    }

    public function deleteUnusedForEmail(string $email): void
    {
        $this->model->where('email', $email)
            ->whereNull('used_at')
            ->delete();
    }

    public function isTokenValid(LoginToken $token): bool
    {
        return $token->used_at === null && $token->expires_at->isFuture();
    }

    public function markAsUsed(LoginToken $token): void
    {
        $token->update(['used_at' => now()]);
    }
}

