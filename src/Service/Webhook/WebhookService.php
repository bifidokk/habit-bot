<?php

declare(strict_types=1);

namespace App\Service\Webhook;

use App\Service\Command\CommandInterface;
use App\Service\Command\CommandResolver;
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
    private CommandResolver $commandResolver;

    public function __construct(
        string $token,
        ServiceLocator $commandLocator,
        LoggerInterface $logger,
        UserService $userService,
        CommandResolver $commandResolver
    ) {
        $this->token = $token;
        $this->commandLocator = $commandLocator;
        $this->logger = $logger;
        $this->userService = $userService;
        $this->commandResolver = $commandResolver;
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

        $commandName = $this->commandResolver->resolve($update->message, $user);

        if ($commandName === null) {
            return;
        }

        try {
            /** @var CommandInterface $command */
            $command = $this->commandLocator->get($commandName);
            $command->run($update->message, $user);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }
}
