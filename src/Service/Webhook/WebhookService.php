<?php

declare(strict_types=1);

namespace App\Service\Webhook;

use App\Entity\User;
use App\Service\Command\AddCustomHabitCommand;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandName;
use App\Service\User\UserService;
use App\Service\User\UserState;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TgBotApi\BotApiBase\Type\MessageEntityType;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;

class WebhookService
{
    private string $token;
    private ServiceLocator $commandLocator;
    private LoggerInterface $logger;
    private UserService $userService;

    public function __construct(
        string $token,
        ServiceLocator $commandLocator,
        LoggerInterface $logger,
        UserService $userService
    ) {
        $this->token = $token;
        $this->commandLocator = $commandLocator;
        $this->logger = $logger;
        $this->userService = $userService;
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

        $commandName = $this->resolveCommand($update->message, $user);

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

    private function resolveCommand(MessageType $message, User $user): ?string
    {
        if (isset($message->entities) && $message->entities !== null) {
            return $this->parseCommandFromMessage($message);
        }

        $command = $this->resolveCommandByName($message);

        if ($command !== null) {
            return $command;
        }

        return $this->resolveCommandByUserState($user);
    }

    private function parseCommandFromMessage(MessageType $message): ?string
    {
        if ($message->entities === null) {
            return null;
        }

        foreach ($message->entities as $entity) {
            if ($entity->type !== MessageEntityType::TYPE_BOT_COMMAND) {
                continue;
            }

            $command = substr($message->text, $entity->offset, $entity->length);

            return substr($command, 1);
        }

        return null;
    }

    private function resolveCommandByName(MessageType $message): ?string
    {
        return CommandName::getName($message->text);
    }

    private function resolveCommandByUserState(User $user): ?string
    {
        switch ($user->getState()) {
            case UserState::NEW_CUSTOM_HABIT:
                return AddCustomHabitCommand::COMMAND_NAME;
        }

        return null;
    }
}
