<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Security;

use App\Entity\User;
use App\EventListener\Security\JWTCreatedListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class JWTCreatedListenerTest extends TestCase
{
    public function testOnJWTCreatedSetsUsername(): void
    {
        $user = new User();
        $reflection = new \ReflectionProperty(User::class, 'id');
        $reflection->setValue($user, Uuid::fromString('550e8400-e29b-41d4-a716-446655440000'));

        $event = $this->createMock(JWTCreatedEvent::class);
        $event->method('getUser')->willReturn($user);
        $event->method('getData')->willReturn([
            'roles' => ['ROLE_USER'],
        ]);

        $event->expects($this->once())
            ->method('setData')
            ->with($this->callback(function (array $payload) {
                return $payload['username'] === '550e8400-e29b-41d4-a716-446655440000'
                    && $payload['roles'] === ['ROLE_USER'];
            }));

        $listener = new JWTCreatedListener();
        $listener->onJWTCreated($event);
    }
}
