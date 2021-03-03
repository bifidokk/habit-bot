<?php

declare(strict_types=1);

namespace App\Service\Webhook;

use App\Service\Command\CommandInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class WebhookService
{
    private string $token;
    private ServiceLocator $commandLocator;
    private LoggerInterface $logger;

    public function __construct(
        string $token,
        ServiceLocator $commandLocator,
        LoggerInterface $logger
    ) {
        $this->token = $token;
        $this->commandLocator = $commandLocator;
        $this->logger = $logger;
    }

    public function isTokenValid(string $token): bool
    {
        return $token === $this->token;
    }

    public function handleMessage(WebhookMessage $webhookMessage): void
    {
        if (!$webhookMessage->isCommand()) {
            return;
        }

        try {
            /** @var CommandInterface $command */
            $command = $this->commandLocator->get($webhookMessage->command);
            $command->run($webhookMessage);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
