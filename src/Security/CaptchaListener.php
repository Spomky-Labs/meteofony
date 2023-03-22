<?php

declare(strict_types=1);

namespace App\Security;

use function is_string;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class CaptchaListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();

        if (! $passport->hasBadge(CaptchaBadge::class)) {
            return;
        }
        $badge = $passport->getBadge(CaptchaBadge::class);
        if ($badge === null) {
            return;
        }

        if ($badge->isResolved()) {
            return;
        }

        $session = $this->requestStack->getSession();
        $expectedCaptcha = $session->remove($badge->getIdentifier());
        if (! is_string($expectedCaptcha)) {
            return;
        }

        if (! hash_equals($expectedCaptcha, $badge->getCaptcha())) {
            throw new InvalidCaptchaException('Invalid captcha.');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['checkPassport', 512],
        ];
    }
}
