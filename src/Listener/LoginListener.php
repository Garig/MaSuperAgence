<?php

namespace App\Listener;

use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;

class LoginListener{

    private $em;

    public function __construct(ObjectManager $em){
        $this->em = $em;
    }

    public function onSecurityAuthenticationSuccess(AuthenticationEvent $event){
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof UserInterface) {
            $user->setLastLogin(new \DateTime());
            $this->em->persist($user);
            $this->em->flush();
        }
    }
}