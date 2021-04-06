<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;

class StartCommand extends AbstractTelegramCommand implements CommandInterface
{
    public const COMMAND_NAME = 'start';
    public const COMMAND_RESPONSE_TEXT = 'Hey there! You can add a new habit here';

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

    public function getPriority(): CommandPriority
    {
        return CommandPriority::get(CommandPriority::HIGH);
    }

    public function run(MessageType $message, User $user): void
    {
        $this->userService->moveUserToStart($user);

        $method = $this->createSendMethod($message);
        $this->bot->sendMessage($method);

        $nextCommand = $this->commandLocator->get(MainMenuCommand::COMMAND_NAME);
        $nextCommand->run($message, $user);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        return SendMessageMethod::create(
            $message->chat->id,
            self::COMMAND_RESPONSE_TEXT
        );
    }
}
