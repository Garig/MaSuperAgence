<?php

namespace App\Notification;

use App\Entity\User;
use Twig\Environment;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class MotPasseNotification
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $renderer;

    public function __construct(\Swift_Mailer $mailer, Environment $renderer)
    {
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }

    public function notify($user, $url)
    {
        $message = (new \Swift_Message('Mot de passe oublié'))
        ->setFrom(['alexandre.houriez5@gmail.com'=> 'Mon Agence'])
        ->setTo($user->getEmail())
        ->setBody($this->renderer->render('security/resetPassword/emails/token.html.twig', [
            'user' => $user,
            'url' => $url
        ]), 'text/html');

        $this->mailer->send($message);        
    }
}