<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

class LoginRedirectSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;
    private Security $security;

    public function __construct(RouterInterface $router, Security $security)
    {
        $this->router = $router;
        $this->security = $security;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InteractiveLoginEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(InteractiveLoginEvent $event)
    {
        $user = $this->security->getUser();
        $roles = $user->getRoles();

        $response = null;

        if (in_array('ROLE_ADMIN', $roles)) {
            $response = new RedirectResponse($this->router->generate('admin_dashboard'));
        } elseif (in_array('ROLE_AGENT', $roles)) {
            $response = new RedirectResponse($this->router->generate('agent_dashboard'));
        } elseif (in_array('ROLE_CLIENT', $roles)) {
            $response = new RedirectResponse($this->router->generate('app_home'));
        }

        if ($response) {
            $event->getRequest()->getSession()->set('_security.main.target_path', $response->getTargetUrl());
        }
    }
}
