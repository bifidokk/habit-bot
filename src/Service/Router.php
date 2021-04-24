<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
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

    public function getCommand(UpdateType $update, User $user): ?CommandInterface
    {
        $commandCallback = $this->inputHandler->checkForInput($user);

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

    public function getCommandByName(string $name): CommandInterface
    {
        return $this->commandLocator->get($name);
    }
}
