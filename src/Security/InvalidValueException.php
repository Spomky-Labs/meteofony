<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class InvalidValueException extends AuthenticationException
{
    /**
     * @var string[]
     */
    private array $messages;

    /**
     * @param string[] $messages
     */
    public function __construct(string $message, array $messages = [], int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->messages = $messages;
    }

    /**
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getMessageKey(): string
    {
        return 'Invalid value.';
    }
}
