<?php

declare(strict_types=1);

namespace App\Service\Webhook;

use App\Service\Router;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TgBotApi\BotApiBase\Type\UpdateType;

class WebhookService
{
    private string $token;
    private ServiceLocator $commandLocator;
    private LoggerInterface $logger;
    private UserService $userService;
    private Router $router;

    public function __construct(
        string $token,
        ServiceLocator $commandLocator,
        LoggerInterface $logger,
        UserService $userService,
        Router $router
    ) {
        $this->token = $token;
        $this->commandLocator = $commandLocator;
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
        if ($update->message === null) {
            return;
        }

        $user = $this->userService->getUser($update->message);

        if ($user === null) {
            return;
        }

        $command = $this->router->getCommand($update->message, $user);

        if ($command === null) {
            return;
        }

        try {
            $command->run($update->message, $user);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }
}
