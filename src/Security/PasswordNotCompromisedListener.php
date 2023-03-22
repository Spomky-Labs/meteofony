<?php

declare(strict_types=1);

namespace App\Security;

use App\Exception\CompromisedPasswordException;
use LogicException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class PasswordNotCompromisedListener implements EventSubscriberInterface
{
    private const DEFAULT_API_ENDPOINT = 'https://api.pwnedpasswords.com/range/%s';

    private readonly HttpClientInterface $httpClient;

    private readonly string $endpoint;

    public function __construct(
        HttpClientInterface $httpClient = null,
        private readonly string $charset = 'UTF-8',
        string $endpoint = null
    ) {
        if ($httpClient === null && ! class_exists(HttpClient::class)) {
            throw new LogicException(sprintf(
                'The "%s" class requires the "HttpClient" component. Try running "composer require symfony/http-client".',
                self::class
            ));
        }

        $this->httpClient = $httpClient ?? HttpClient::create();
        $this->endpoint = $endpoint ?? self::DEFAULT_API_ENDPOINT;
    }

    public function checkPassport(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        if (! $passport->hasBadge(PasswordCredentials::class)) {
            return;
        }
        if (! $passport->hasBadge(PasswordNotCompromisedBadge::class)) {
            return;
        }
        $passwordBadge = $passport->getBadge(PasswordCredentials::class);
        if ($passwordBadge === null) {
            return;
        }
        $pwdNotCompromisedBadge = $passport->getBadge(PasswordNotCompromisedBadge::class);
        if ($pwdNotCompromisedBadge === null || $pwdNotCompromisedBadge->isResolved()) {
            return;
        }

        $value = $passwordBadge->getPassword();

        if ($value === '') {
            return;
        }

        if ($this->charset !== 'UTF-8') {
            $value = mb_convert_encoding($value, 'UTF-8', $this->charset);
        }

        $hash = mb_strtoupper(sha1($value));
        $hashPrefix = mb_substr($hash, 0, 5);
        $url = sprintf($this->endpoint, $hashPrefix);

        try {
            $result = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Add-Padding' => 'true',
                ],
            ])->getContent();
        } catch (Throwable) {
            if ($pwdNotCompromisedBadge->skipOnError) {
                $pwdNotCompromisedBadge->markResolved();
            }
            return;
        }

        foreach (explode("\r\n", $result) as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }

            [$hashSuffix, $count] = explode(':', $line);

            if ($hashPrefix . $hashSuffix === $hash && $pwdNotCompromisedBadge->threshold <= (int) $count) {
                throw new CompromisedPasswordException(
                    'Ce mot de passe est compromis et n’est pas autorisé sur cette plateforme.'
                );
            }
        }

        $pwdNotCompromisedBadge->markResolved();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['checkPassport', 512],
        ];
    }
}
