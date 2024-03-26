<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class PasswordAgeListener implements EventSubscriberInterface
{
    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            //CheckPassportEvent::class => 'checkPasswordStrength',
        ];
    }

    public function checkPasswordStrength(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();

        if (!$passport->hasBadge(UserBadge::class)) {
            return;
        }

        $badge = $passport->getBadge(UserBadge::class);
        $user = $badge->getUser();
        if (!$user instanceof User) {
            return;
        }
        $lastPasswordChange = $user->getLastPasswordChange();
        if (null === $lastPasswordChange || $lastPasswordChange->diff(new \DateTime())->days < 365) {
            //throw new PasswordIsTooOldException();
        }

        $badge->markAsResolved();
    }
}
