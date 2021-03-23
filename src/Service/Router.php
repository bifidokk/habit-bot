<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Service\Command\CommandInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TgBotApi\BotApiBase\Type\MessageType;

class Router
{
    private ServiceLocator $commandLocator;

    public function __construct(ServiceLocator $commandLocator)
    {
        $this->commandLocator = $commandLocator;
    }

    public function getCommand(MessageType $message, User $user): ?CommandInterface
    {
        $commands = $this->commandLocator->getProvidedServices();

        foreach ($commands as $commandName => $commandClass) {
            /** @var CommandInterface $command */
            $command = $this->commandLocator->get($commandName);

            if ($command->canRun($message, $user)) {
                return $command;
            }
        }

        return null;
    }
}
