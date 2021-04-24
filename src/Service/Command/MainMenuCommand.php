<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Keyboard\MainMenuKeyboard;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;

class MainMenuCommand implements CommandInterface
{
    public const COMMAND_NAME = 'main_menu';
    public const COMMAND_RESPONSE_TEXT = 'You are in the main menu';

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

    public function getPriority(): CommandPriority
    {
        return CommandPriority::get(CommandPriority::LOW);
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return false;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $this->userService->moveUserToStart($user);

        $method = $this->createSendMethod($update->message);
        $this->bot->sendMessage($method);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        return SendMessageMethod::create(
            $message->chat->id,
            self::COMMAND_RESPONSE_TEXT, [
                'replyMarkup' => MainMenuKeyboard::generate(),
            ]);
    }
}
