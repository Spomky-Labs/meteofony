<?php

declare(strict_types=1);

namespace App\Security;

use function is_string;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

final readonly class CaptchaListener implements EventSubscriberInterface
{
    public const SESSION_KEY_PREFIX = '_captcha_';

    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        if (! $passport->hasBadge(CaptchaBadge::class)) {
            return;
        }
        /** @var CaptchaBadge $badge */
        $badge = $passport->getBadge(CaptchaBadge::class);
        if ($badge->isResolved()) {
            return;
        }

        $session = $this->requestStack->getSession();
        $expectedPhrase = $session->remove(sprintf('%s%s', self::SESSION_KEY_PREFIX, $badge->getIdentifier()));
        if (! is_string($expectedPhrase)) {
            return;
        }

        if (! hash_equals($expectedPhrase, $badge->getPhrase())) {
            throw new InvalidCaptchaException('Invalid captcha.');
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
