<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Service\Command\CommandInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TgBotApi\BotApiBase\Type\UpdateType;

class Router
{
    private ServiceLocator $commandLocator;

    public function __construct(ServiceLocator $commandLocator)
    {
        $this->commandLocator = $commandLocator;
    }

    public function getCommand(UpdateType $update, User $user): ?CommandInterface
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
            if ($command->canRun($update, $user)) {
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
