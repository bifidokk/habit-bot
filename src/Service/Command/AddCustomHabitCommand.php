<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\MessageType;

class AddCustomHabitCommand implements CommandInterface
{
    public const COMMAND_NAME = 'add_custom_habit';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private UserService $userService;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        UserService $userService
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->userService = $userService;
    }

    public function getName(): string
    {
        return self::COMMAND_NAME;
    }

    public function run(MessageType $message, User $user): void
    {
        $this->logger->info('AddCustomHabit');
    }
}
