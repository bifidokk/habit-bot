<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TgBotApi\BotApiBase\Type\MessageType;

class BackCommand implements CommandInterface
{
    public const COMMAND_NAME = 'back';
    public const COMMAND_PHRASE = 'Back';

    private ServiceLocator $commandLocator;

    public function __construct(
        ServiceLocator $commandLocator
    ) {
        $this->commandLocator = $commandLocator;
    }

    public function getName(): string
    {
        return self::COMMAND_NAME;
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

        $command = $this->commandLocator->get($commandName);
        $command->run($message, $user);
    }
}
