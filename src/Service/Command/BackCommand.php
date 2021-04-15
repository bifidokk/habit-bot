<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Router;
use TgBotApi\BotApiBase\Type\MessageType;

class BackCommand implements CommandInterface
{
    public const COMMAND_NAME = 'back';
    public const COMMAND_PHRASE = 'Back';

    private Router $router;

    public function __construct(
        Router $router
    ) {
        $this->router = $router;
    }

    public function getName(): string
    {
        return self::COMMAND_NAME;
    }

    public function getPriority(): CommandPriority
    {
        return CommandPriority::get(CommandPriority::LOW);
    }

    public function canRun(MessageType $message, User $user): bool
    {
        return $message->text === self::COMMAND_PHRASE;
    }

    public function run(MessageType $message, User $user): void
    {
        switch ($user->getState()) {
            default:
                $commandName = MainMenuCommand::COMMAND_NAME;
        }

        $command = $this->router->getCommandByName($commandName);
        $command->run($message, $user);
    }
}
