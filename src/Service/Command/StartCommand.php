<?php

declare(strict_types=1);

namespace App\Service\Command;

use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
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
    }
}
