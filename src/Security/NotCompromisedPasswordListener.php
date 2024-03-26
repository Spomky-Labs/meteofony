<?php

namespace App\Security;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NotCompromisedPasswordListener implements EventSubscriberInterface
{
    private const DEFAULT_API_ENDPOINT = 'https://api.pwnedpasswords.com/range/%s';

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => 'checkCompromisedPassword',
        ];
    }

    public function checkCompromisedPassword(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        if (!$passport->hasBadge(NotCompromisedPasswordBadge::class)) {
            return;
        }

        $badge = $passport->getBadge(NotCompromisedPasswordBadge::class);
        if ($badge->isResolved()) {
            return;
        }
        $password = $badge->getPassword();

        if ($this->isPasswordCompromised($password)) {
            throw new CompromisedPasswordException();
        }

        $badge->markAsResolved();
    }

    private function isPasswordCompromised(string $password): bool
    {
        $hash = strtoupper(sha1($password));
        $hashPrefix = substr($hash, 0, 5);
        $url = sprintf(self::DEFAULT_API_ENDPOINT, $hashPrefix);

        try {
            $result = $this->httpClient->request('GET', $url, ['headers' => ['Add-Padding' => 'true']])->getContent();
        } catch (\Throwable) {
            return false;
        }

        foreach (explode("\r\n", $result) as $line) {
            if (!str_contains($line, ':')) {
                continue;
            }

            [$hashSuffix] = explode(':', $line);

            if ($hashPrefix.$hashSuffix === $hash) {
                return true;
            }
        }

        return false;
    }
}
