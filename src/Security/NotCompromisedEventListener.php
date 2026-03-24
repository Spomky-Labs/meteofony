<?php

namespace App\Security;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsEventListener]
class NotCompromisedEventListener
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    public function __invoke(CheckPassportEvent $event): void
    {
        $passport = $event->getPassport();
        $badge = $passport->getBadge(NotCompromisedBadge::class);
        if($badge === null) {
            return;
        }

        $errors = $this->validator->validate(
            $badge->getPassword(),
            [new NotCompromisedPassword()]
        );

        if (count($errors) === 0) {
            $badge->markAsResolved();
            return;
        }

        //throw new AuthenticationException($errors[0]->getMessage());
    }
}
