<?php
/**
 * Created by IntelliJ IDEA.
 * User: Gilles
 * Date: 13/01/2019
 * Time: 17:04
 */

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener {

    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if($user instanceof UserInterface){
            $user->updateLastLogin();
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public function onLoginError(AuthenticationEvent $event)
    {
        // Login error
    }
}