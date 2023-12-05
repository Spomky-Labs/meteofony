<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\SecurityEvent;
use App\Entity\User;
use App\Repository\SecurityEventRepository;
use App\Repository\UserRepository;
use Psr\Clock\ClockInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Uid\Ulid;

final readonly class SecuritySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private SecurityEventRepository $securityEventRepository,
        private ClockInterface $clock,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TwoFactorAuthenticationEvents::FAILURE => [['logMFAFailureEvent', 256]],
            TwoFactorAuthenticationEvents::SUCCESS => [['logMFASuccessEvent', 256]],
            LogoutEvent::class => [['logSecurityEvent', 256]],
            LoginSuccessEvent::class => [['logSecurityEvent', 256], ['onLogin']],
            LoginFailureEvent::class => ['logSecurityEvent', 256],
        ];
    }

    public function logMFAFailureEvent(TwoFactorAuthenticationEvent $event): void
    {
        $user = $event->getToken()
            ->getUser();
        if (! $user instanceof User) {
            return;
        }

        $this->securityEventRepository->save(new SecurityEvent(
            Ulid::generate(),
            SecurityEvent::EVENT_MFA_FAILURE,
            $user,
            $this->clock->now(),
            $event->getRequest()
                ->getClientIp(),
            $event->getRequest()
                ->headers->get('User-Agent'),
        ));
    }

    public function logMFASuccessEvent(TwoFactorAuthenticationEvent $event): void
    {
        $user = $event->getToken()
            ->getUser();
        if (! $user instanceof User) {
            return;
        }

        $this->securityEventRepository->save(new SecurityEvent(
            Ulid::generate(),
            SecurityEvent::EVENT_MFA_SUCCESS,
            $user,
            $this->clock->now(),
            $event->getRequest()
                ->getClientIp(),
            $event->getRequest()
                ->headers->get('User-Agent'),
        ));
    }

    public function logSecurityEvent(LogoutEvent|LoginSuccessEvent|LoginFailureEvent $event): void
    {
        $user = null;
        $eventType = null;
        if ($event instanceof LoginSuccessEvent) {
            $user = $event->getUser();
            $eventType = $event->getAuthenticatedToken() instanceof RememberMeToken ? SecurityEvent::EVENT_LOGIN_REMEMBERME_SUCCESS : SecurityEvent::EVENT_LOGIN_SUCCESS;
        } elseif ($event instanceof LoginFailureEvent) {
            $user = $event->getException()
                ->getToken()?->getUser();
            $eventType = SecurityEvent::EVENT_LOGIN_FAILURE;
        } elseif ($event instanceof LogoutEvent) {
            $user = $event->getToken()?->getUser();
            $eventType = SecurityEvent::EVENT_LOGOUT;
        }
        if (! $user instanceof User) {
            return;
        }

        $this->securityEventRepository->save(new SecurityEvent(
            Ulid::generate(),
            $eventType,
            $user,
            $this->clock->now(),
            $event->getRequest()
                ->getClientIp(),
            $event->getRequest()
                ->headers->get('User-Agent'),
        ));
    }

    public function onLogin(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (! $user instanceof User) {
            return;
        }
        $user->setLastLoginAt($this->clock->now());
        $this->userRepository->save($user);
    }
}
