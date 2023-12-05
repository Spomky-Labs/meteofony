<?php

declare(strict_types=1);

namespace App\UserSession;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserSessionInterface
{
    public function getId(): string;

    /**
     * @return string|resource
     */
    public function getData();

    public function setData(string $data): void;

    public function getLifetime(): int;

    public function setLifetime(int $lifetime): void;

    public function getTime(): int;

    public function getUser(): ?UserInterface;

    public function setUser(UserInterface $user): void;
}
