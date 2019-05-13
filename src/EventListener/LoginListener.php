<?php
/**
 * Created by IntelliJ IDEA.
 * User: Gilles
 * Date: 13/01/2019
 * Time: 17:04
 */

namespace App\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class LoginListener {

    protected $entityManager;

    public function __construct(EntityManager $entityManager){
        $this->entityManager = $entityManager;
    }

    public function onSuccessfulLogin(AuthenticationEvent $event)
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