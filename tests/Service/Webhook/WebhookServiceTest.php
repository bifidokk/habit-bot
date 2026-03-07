<?php

declare(strict_types=1);

namespace App\Tests\Service\Webhook;

use App\Entity\User;
use App\Service\Router;
use App\Service\User\UserService;
use App\Service\Webhook\WebhookService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;
use TgBotApi\BotApiBase\Type\UserType;

class WebhookServiceTest extends TestCase
{
    private WebhookService $webhookService;

    private LoggerInterface&MockObject $logger;

    private UserService&MockObject $userService;

    private Router&MockObject $router;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userService = $this->createMock(UserService::class);
        $this->router = $this->createMock(Router::class);

        $this->webhookService = new WebhookService(
            'secret-token',
            $this->logger,
            $this->userService,
            $this->router,
        );
    }

    public function testIsTokenValid(): void
    {
        $this->assertTrue($this->webhookService->isTokenValid('secret-token'));
    }

    public function testIsTokenInvalid(): void
    {
        $this->assertFalse($this->webhookService->isTokenValid('wrong-token'));
    }

    public function testHandleMessageRoutesToCorrectCommand(): void
    {
        $user = new User();
        $update = $this->createUpdate();

        $this->userService
            ->expects($this->once())
            ->method('getUser')
            ->with($update)
            ->willReturn($user);

        $this->router
            ->expects($this->once())
            ->method('run')
            ->with($update, $user);

        $this->webhookService->handleMessage($update);
    }

    public function testHandleMessageWithNullUpdate(): void
    {
        $update = new UpdateType();
        $update->message = null;
        $update->callbackQuery = null;

        $this->userService
            ->expects($this->never())
            ->method('getUser');

        $this->router
            ->expects($this->never())
            ->method('run');

        $this->webhookService->handleMessage($update);
    }

    private function createUpdate(): UpdateType
    {
        $userType = new UserType();
        $userType->id = 12345;
        $userType->firstName = 'Test';

        $message = new MessageType();
        $message->from = $userType;
        $message->text = '/start';

        $update = new UpdateType();
        $update->message = $message;

        return $update;
    }
}
