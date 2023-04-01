<?php

namespace App\EventSubscriber;

use App\Entity\SecurityEvent;
use App\Entity\User;
use App\Entity\UserSession;
use App\Repository\SecurityEventRepository;
use App\Repository\UserSessionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class SecuritySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserSessionRepository $userSessionRepo,
        private readonly SecurityEventRepository $securityEventRepository,
    ) {
    }

    /**
     * @return array<string, array<int, string|int>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => [
                ['onKernelRequest', 20],
            ],
            LogoutEvent::class => [
                ['onLogout', 20],
            ],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (! $event->isMainRequest()) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        if (! $user instanceof UserInterface) {
            return;
        }

        $session = $event->getRequest()->getSession();
        $sessionId = $session->getId();

        $userSession = $this->userSessionRepo->findOneBy(['sessionId' => $sessionId]);

        if ($userSession !== null) {
            return;
        }

        $userSession = UserSession::create($user, $sessionId);
        $this->userSessionRepo->save($userSession);
    }

    public function onLogout(LogoutEvent $event)
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $securityEvent = SecurityEvent::create(
            SecurityEvent::EVENT_LOGOUT,
            $user,
            new \DateTimeImmutable(),
            $event->getRequest()->getClientIp(),
            $event->getRequest()->headers->get('User-Agent'),
        );

        $this->securityEventRepository->save($securityEvent);
    }
}
