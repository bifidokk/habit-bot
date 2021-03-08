<?php

declare(strict_types=1);

namespace App\Service\Command;

use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;

class NewCustomHabitCommand implements CommandInterface
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
        $method = $this->createSendMethod($message);
        $this->bot->sendMessage($method);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        return SendMessageMethod::create($message->chat->id, 'Just enter a new habit\'s text');
    }
}
