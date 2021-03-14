<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\KeyboardButtonType;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\ReplyKeyboardMarkupType;

class StartCommand implements CommandInterface
{
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

    public const COMMAND_NAME = 'start';

    public function getName(): string
    {
        return self::COMMAND_NAME;
    }

    public function run(MessageType $message, User $user): void
    {
        $this->userService->moveUserTostart($user);

        $method = $this->createSendMethod($message);
        $this->bot->sendMessage($method);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        return SendMessageMethod::create(
            $message->chat->id,
            'Hey there! You can add a new habit here', [
                'replyMarkup' => ReplyKeyboardMarkupType::create([
                    [KeyboardButtonType::create('Add a new habit')],
                ]),
            ]);
    }
}
