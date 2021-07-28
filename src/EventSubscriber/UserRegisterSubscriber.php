<?php


namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Security\TokenGenerator;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegisterSubscriber implements EventSubscriberInterface
{
    /**
     * PasswordHashSubscriber constructor.
     */
    private $passwordEncoder;
    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;
    /**
     * @var \swift_Mailer
     */
    private $mailer;

    public function __construct(
        UserPasswordHasherInterface $passwordEncoder,
        TokenGenerator $tokenGenerator,
            \swift_Mailer $mailer
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['userRegistered', EventPriorities::PRE_WRITE]
        ];
    }

    public function userRegistered(ViewEvent $event)
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$user instanceof User || ! in_array($method, [Request::METHOD_POST])) {
            return;
        }

        // it is an User, we need to hash password here
        $user->setPassword(
          $this->passwordEncoder->hashPassword($user, $user->getPassword())
        );

        // Create confirmation token
        $user->setConfirmationToken(
            $this->tokenGenerator->getRandomSecureToken()
        );

        // send e-mail here...
        $message = (new Swift_Message('Hello From API PLATFORM!'))
            ->setFrom('nourudemy@gmail.com')
            ->setTo('nourudemy@gmail.com')
            ->setBody('Hello, how are you?');

        $this->mailer->send($message);

        }

}