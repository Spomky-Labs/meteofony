<?php

namespace App\Security;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class PasswordStrengthListener implements EventSubscriberInterface
{
    public const STRENGTH_VERY_WEAK = 0;
    public const STRENGTH_WEAK = 1;
    public const STRENGTH_MEDIUM = 2;
    public const STRENGTH_STRONG = 3;
    public const STRENGTH_VERY_STRONG = 4;

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => 'checkPasswordStrength',
        ];
    }

    public function checkPasswordStrength(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();

        if (!$passport->hasBadge(PasswordStrengthBadge::class)) {
            return;
        }

        $badge = $passport->getBadge(PasswordStrengthBadge::class);
        if ($badge->isResolved()) {
            return;
        }
        $password = $badge->getPassword();

        if (self::estimateStrength($password) < self::STRENGTH_MEDIUM) {
            throw new PasswordStrengthException();
        }

        $badge->markAsResolved();
    }

    /**
     * Returns the estimated strength of a password.
     *
     * The higher the value, the stronger the password.
     *
     * @return PasswordStrength::STRENGTH_*
     */
    private static function estimateStrength(#[\SensitiveParameter] string $password): int
    {
        if (!$length = \strlen($password)) {
            return self::STRENGTH_VERY_WEAK;
        }
        $password = count_chars($password, 1);
        $chars = \count($password);

        $control = $digit = $upper = $lower = $symbol = $other = 0;
        foreach ($password as $chr => $count) {
            match (true) {
                $chr < 32 || 127 === $chr => $control = 33,
                48 <= $chr && $chr <= 57 => $digit = 10,
                65 <= $chr && $chr <= 90 => $upper = 26,
                97 <= $chr && $chr <= 122 => $lower = 26,
                128 <= $chr => $other = 128,
                default => $symbol = 33,
            };
        }

        $pool = $lower + $upper + $digit + $symbol + $control + $other;
        $entropy = $chars * log($pool, 2) + ($length - $chars) * log($chars, 2);

        return match (true) {
            $entropy >= 120 => self::STRENGTH_VERY_STRONG,
            $entropy >= 100 => self::STRENGTH_STRONG,
            $entropy >= 80 => self::STRENGTH_MEDIUM,
            $entropy >= 60 => self::STRENGTH_WEAK,
            default => self::STRENGTH_VERY_WEAK,
        };
    }
}
