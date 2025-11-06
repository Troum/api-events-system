<?php

namespace App\Repositories\Contracts;

use App\Models\LoginToken;

interface LoginTokenRepositoryInterface extends RepositoryInterface
{
    /**
     * Создать токен для email
     */
    public function createForEmail(string $email): LoginToken;

    /**
     * Найти токен по значению
     */
    public function findByToken(string $token): ?LoginToken;

    /**
     * Удалить старые неиспользованные токены для email
     */
    public function deleteUnusedForEmail(string $email): void;

    /**
     * Проверить валидность токена
     */
    public function isTokenValid(LoginToken $token): bool;

    /**
     * Отметить токен как использованный
     */
    public function markAsUsed(LoginToken $token): void;
}

