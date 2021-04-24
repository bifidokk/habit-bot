<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TgBotApi\BotApiBase\Type\UpdateType;

class Router
{
    private ServiceLocator $commandLocator;
    private InputHandler $inputHandler;
    private LoggerInterface $logger;

    public function __construct(
        ServiceLocator $commandLocator,
        InputHandler $inputHandler,
        LoggerInterface $logger
    ) {
        $this->commandLocator = $commandLocator;
        $this->inputHandler = $inputHandler;
        $this->logger = $logger;
    }

    public function run(UpdateType $update, User $user): void
    {
        $commandCallback = $this->getCommandCallback($update, $user);
        $command = $this->getCommand($update, $user, $commandCallback);

        if ($command instanceof CommandInterface) {
            $command->run($update, $user, $commandCallback);
        }
    }

    public function getCommandByName(string $name): CommandInterface
    {
        return $this->commandLocator->get($name);
    }

    private function getCommand(UpdateType $update, User $user, ?CommandCallback $commandCallback): ?CommandInterface
    {
        $commandServices = $this->commandLocator->getProvidedServices();
        $commands = [];

        foreach ($commandServices as $commandName => $commandClass) {
            $commands[] = $this->commandLocator->get($commandName);
        }

        usort($commands, function ($a, $b) {
            return $b->getPriority()->getValue() <=> $a->getPriority()->getValue();
        });

        foreach ($commands as $command) {
            if ($command->canRun($update, $user, $commandCallback)) {
                return $command;
            }
        }

        return null;
    }

    private function getCommandCallback(UpdateType $update, User $user): ?CommandCallback
    {
        $commandCallbackEnum = $this->inputHandler->checkForInput($user);

        if ($commandCallbackEnum !== null) {
            $commandCallback = new CommandCallback();
            $commandCallback->command = $commandCallbackEnum;

            return $commandCallback;
        }

        if ($update->callbackQuery !== null && $update->callbackQuery->data !== null) {
            return $this->parseCallbackQueryData($update->callbackQuery->data);
        }

        return null;
    }

    private function parseCallbackQueryData(string $data): ?CommandCallback
    {
        $callbackData = parse_url($data);

        if (!isset($callbackData['path'])) {
            return null;
        }

        $commandCallbackEnum = CommandCallbackEnum::get($callbackData['path']);
        $commandCallback = new CommandCallback();
        $commandCallback->command = $commandCallbackEnum;

        if (isset($callbackData['query'])) {
            parse_str($callbackData['query'], $commandCallback->parameters);
        }

        return $commandCallback;
    }
}
