<?php

declare(strict_types=1);

namespace App\Service\Command;

use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\MessageType;

class StartCommand implements CommandInterface
{
    private BotApiComplete $bot;
    private LoggerInterface $logger;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
    }

    public function run(MessageType $message): void
    {
        $this->logger->info($message->text);

        $method = $this->createSendMethod($message);
        $this->bot->sendMessage($method);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        return SendMessageMethod::create($message->chat->id, 'Hey there! You can add a new habit here', [
            'replyMarkup' => InlineKeyboardMarkupType::create([
                [InlineKeyboardButtonType::create('Add a new habit', [
                    'callbackData' => 'new_habit',
                ])],
            ]),
        ]);
    }
}
