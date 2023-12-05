<?php

declare(strict_types=1);

namespace App\UserSession;

interface UserSessionRepositoryInterface
{
    public function remove(UserSessionInterface $userSession): void;

    public function save(UserSessionInterface $userSession): void;

    public function findOneById(string $sessionId): ?UserSessionInterface;

    public function removeExpired(): void;

    public function create(string $sessionId, string $data, int $maxLifetime, int $getTimestamp): UserSessionInterface;
}
