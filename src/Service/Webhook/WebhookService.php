<?php

declare(strict_types=1);

namespace App\Service\Webhook;

use App\Service\Command\CommandInterface;
use App\Service\Command\CommandNameList;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TgBotApi\BotApiBase\Type\MessageEntityType;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;

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

    public function handleMessage(UpdateType $update): void
    {
        if ($update->message === null) {
            return;
        }

        $commandName = $this->resolveCommand($update->message);

        if ($commandName === null) {
            return;
        }

        try {
            /** @var CommandInterface $command */
            $command = $this->commandLocator->get($commandName);
            $command->run($update->message);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function resolveCommand(MessageType $message): ?string
    {
        if (isset($message->entities) && $message->entities !== null) {
            return $this->parseCommandFromMessage($message);
        }

        return $this->chooseCommand($message);
    }

    private function parseCommandFromMessage(MessageType $message): ?string
    {
        if ($message->entities === null) {
            return null;
        }

        foreach ($message->entities as $entity) {
            if ($entity->type !== MessageEntityType::TYPE_BOT_COMMAND) {
                continue;
            }

            $command = substr($message->text, $entity->offset, $entity->length);

            return substr($command, 1);
        }

        return null;
    }

    private function chooseCommand(MessageType $message): ?string
    {
        return CommandNameList::getName($message->text);
    }
}
