<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GroupSequence;

final class ConstraintBadge implements BadgeInterface
{
    private bool $isResolved = false;

    /**
     * @param Constraint[] $constraints
     */
    public function __construct(
        private readonly mixed $value,
        private readonly array $constraints,
        private readonly null|string|GroupSequence|array $groups = null,
    ) {
        if (!class_exists(Constraint::class)) {
            throw new LogicException(sprintf('The "%s" class requires the "symfony/validator" component. Try running "composer require symfony/validator".', self::class));
        }
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getGroups(): array|GroupSequence|string|null
    {
        return $this->groups;
    }

    /**
     * @return Constraint[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function markResolved(): void
    {
        $this->isResolved = true;
    }

    public function isResolved(): bool
    {
        return $this->isResolved;
    }
}
