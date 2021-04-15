<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Router;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
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
    private Router $router;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        UserService $userService,
        Router $router
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->userService = $userService;
        $this->router = $router;
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

        $nextCommand = $this->router->getCommandByName(MainMenuCommand::COMMAND_NAME);
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
