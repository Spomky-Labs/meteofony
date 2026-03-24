<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

#[AsEventListener]
class OldPasswordEventListener
{
    public function __construct(private readonly ClockInterface $clock)
    {
    }

    public function __invoke(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        $badge = $passport->getBadge(OldPasswordBadge::class);
        if($badge === null || $badge->isResolved()) {
            return;
        }

        $userBadge = $passport->getBadge(UserBadge::class);
        if ($userBadge === null || !$userBadge->isResolved()) {
            return;
        }

        $user = $userBadge->getUser();
        assert($user instanceof User);

        // Logique ici. Prévenir l'utilisateur ou le bloquer

        $badge->markAsResolved();
    }
}
