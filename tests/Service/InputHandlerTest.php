<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\InputHandler;
use App\Service\Redis\RedisClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InputHandlerTest extends TestCase
{
    private InputHandler $inputHandler;

    private RedisClient&MockObject $redisClient;

    protected function setUp(): void
    {
        $this->redisClient = $this->createMock(RedisClient::class);
        $this->inputHandler = new InputHandler($this->redisClient);
    }

    public function testWaitAndCheckForInput(): void
    {
        $user = new User();
        $reflection = new \ReflectionProperty(User::class, 'telegramId');
        $reflection->setValue($user, 12345);

        $this->redisClient->expects($this->once())
            ->method('set')
            ->with('wait_for_input:12345', '/done?id=abc');

        $this->inputHandler->waitForInput($user, '/done?id=abc');

        $this->redisClient->method('get')
            ->with('wait_for_input:12345')
            ->willReturn('/done?id=abc');

        $result = $this->inputHandler->checkForInput($user);
        $this->assertSame('/done?id=abc', $result);
    }

    public function testUnwaitAndCheckForInput(): void
    {
        $user = new User();
        $reflection = new \ReflectionProperty(User::class, 'telegramId');
        $reflection->setValue($user, 12345);

        $this->redisClient->expects($this->once())
            ->method('del')
            ->with('wait_for_input:12345')
            ->willReturn(1);

        $result = $this->inputHandler->unwaitForInput($user);
        $this->assertSame(1, $result);

        $this->redisClient->method('get')
            ->with('wait_for_input:12345')
            ->willReturn(false);

        $checkResult = $this->inputHandler->checkForInput($user);
        $this->assertNull($checkResult);
    }

    public function testCheckForInputReturnsNullWhenNotSet(): void
    {
        $user = new User();
        $reflection = new \ReflectionProperty(User::class, 'telegramId');
        $reflection->setValue($user, 12345);

        $this->redisClient->method('get')
            ->with('wait_for_input:12345')
            ->willReturn(false);

        $result = $this->inputHandler->checkForInput($user);
        $this->assertNull($result);
    }
}
