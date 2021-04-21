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
use TgBotApi\BotApiBase\Type\UpdateType;

class StartCommand implements CommandInterface
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

    public function canRun(UpdateType $update, User $user): bool
    {
        return $update->message !== null && sprintf('/%s', $this->getName()) === $update->message->text;
    }

    public function run(UpdateType $update, User $user): void
    {
        $this->userService->moveUserToStart($user);

        $method = $this->createSendMethod($update->message);
        $this->bot->sendMessage($method);

        $nextCommand = $this->router->getCommandByName(MainMenuCommand::COMMAND_NAME);
        $nextCommand->run($update, $user);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        return SendMessageMethod::create(
            $message->chat->id,
            self::COMMAND_RESPONSE_TEXT
        );
    }
}
