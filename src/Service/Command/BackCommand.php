<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\MessageType;

class BackCommand implements CommandInterface
{
    public const COMMAND_NAME = 'back';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private UserService $userService;
    private ServiceLocator $commandLocator;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        UserService $userService,
        ServiceLocator $commandLocator
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->userService = $userService;
        $this->commandLocator = $commandLocator;
    }

    public function getName(): string
    {
        return self::COMMAND_NAME;
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
