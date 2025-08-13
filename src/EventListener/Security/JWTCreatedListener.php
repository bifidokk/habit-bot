<?php
declare(strict_types=1);

namespace App\EventListener\Security;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();
        $payload = $event->getData();

        $payload['username'] = (string) $user->getId();

        $event->setData($payload);
    }
}
