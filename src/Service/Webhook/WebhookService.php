<?php

declare(strict_types=1);

namespace App\Service\Webhook;

use App\Service\Router;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\Type\UpdateType;

class WebhookService
{
    private string $token;
    private LoggerInterface $logger;
    private UserService $userService;
    private Router $router;

    public function __construct(
        string $token,
        LoggerInterface $logger,
        UserService $userService,
        Router $router
    ) {
        $this->token = $token;
        $this->logger = $logger;
        $this->userService = $userService;
        $this->router = $router;
    }

    public function isTokenValid(string $token): bool
    {
        return $token === $this->token;
    }

    public function handleMessage(UpdateType $update): void
    {
        if ($update->message === null && $update->callbackQuery === null) {
            return;
        }

        $user = $this->userService->getUser($update);

        if ($user === null) {
            return;
        }

        try {
            $this->router->run($update, $user);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
