<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function onLogoutEvent(LogoutEvent $event)
    {
        // Vider le panier
        $this->session->remove('cart');
    }
}
