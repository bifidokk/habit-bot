<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Service\Webhook\WebhookMessage;
use Psr\Log\LoggerInterface;

class StartCommand implements CommandInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function run(WebhookMessage $message): void
    {
        $this->logger->info($message->command);
    }
}
