<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function is_string;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

final readonly class ConstraintBadgeListener implements EventSubscriberInterface
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        if (! $passport->hasBadge(ConstraintBadge::class)) {
            return;
        }

        $badge = $passport->getBadge(ConstraintBadge::class);
        if ($badge->isResolved()) {
            return;
        }

        $violationList = $this->validator->validate(
            $badge->getValue(),
            $badge->getConstraints(),
            $badge->getGroups()
        );

        if (0 !== $violationList->count()) {
            $messages = array_reduce($violationList, fn(array $messages, ConstraintViolationInterface $violation): array => $violation->getMessage(), []);
            throw new InvalidValueException('Invalid value.', $messages);
        }

        $badge->markResolved();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['checkPassport', 512],
        ];
    }
}
