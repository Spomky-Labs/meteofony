<?php

declare(strict_types=1);

namespace App\Security;

use App\Exception\PasswordTooLongException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

final class PasswordMaximumLengthListener implements EventSubscriberInterface
{
    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        if (! $passport->hasBadge(PasswordCredentials::class)) {
            return;
        }
        if (! $passport->hasBadge(PasswordMaximumLengthBadge::class)) {
            return;
        }
        $passwordBadge = $passport->getBadge(PasswordCredentials::class);
        if ($passwordBadge === null) {
            return;
        }
        $pwdMaxLengthBadge = $passport->getBadge(PasswordMaximumLengthBadge::class);
        if ($pwdMaxLengthBadge === null || $pwdMaxLengthBadge->isResolved()) {
            return;
        }

        $pwdLength = mb_strlen($passwordBadge->getPassword(), '8bit');

        if ($pwdLength > $pwdMaxLengthBadge->maxLength) {
            throw new PasswordTooLongException('Le mot de passe est trop long.');
        }
        $pwdMaxLengthBadge->markResolved();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['checkPassport', 512],
        ];
    }
}
