<?php


namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Security\TokenGenerator;
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

    public function __construct(
        UserPasswordHasherInterface $passwordEncoder,
        TokenGenerator $tokenGenerator
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenGenerator = $tokenGenerator;
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

        }

}