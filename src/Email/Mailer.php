<?php


namespace App\Email;


use App\Entity\User;
use Swift_Message;
use Twig\Environment as Twig;


class Mailer
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var Twig
     */
    private $twig;

    public function __construct(
        \Swift_Mailer $mailer,
        Twig $twig
    )
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendConfirmationEmail(User $user)
    {
        $body = $this->twig->render(
            'email/confirmation.html.twig',
            [
                'user' => $user
            ]
        );

        $message = (new Swift_Message('Please confirm your account!'))
            ->setFrom('nourudemy@gmail.com')
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }

}