<?php

declare(strict_types=1);

namespace App\Security;

use App\Exception\CompromisedPasswordException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class PasswordNotCompromisedBadgeListener implements EventSubscriberInterface
{
    private const DEFAULT_API_ENDPOINT = 'https://api.pwnedpasswords.com/range/%s';

    private readonly string $endpoint;

    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {
        $this->endpoint = self::DEFAULT_API_ENDPOINT;
    }

    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        if (! $passport->hasBadge(PasswordNotCompromisedBadge::class)) {
            return;
        }

        /** @var PasswordNotCompromisedBadge $badge */
        $badge = $passport->getBadge(PasswordNotCompromisedBadge::class);
        if ($badge->isResolved()) {
            return;
        }

        $hash = mb_strtoupper(sha1($badge->getPassword()));
        $hashPrefix = mb_substr($hash, 0, 5);
        $url = sprintf($this->endpoint, $hashPrefix);

        try {
            $result = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Add-Padding' => 'true',
                ],
            ])->getContent();
        } catch (Throwable) {
            $badge->markResolved();
            return;
        }

        foreach (explode("\r\n", $result) as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }

            [$hashSuffix, $count] = explode(':', $line);

            if ($hashPrefix . $hashSuffix === $hash && (int) $count >= 1) {
                throw new CompromisedPasswordException();
            }
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
