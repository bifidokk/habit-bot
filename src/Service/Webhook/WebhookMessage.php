<?php

declare(strict_types=1);

namespace App\Service\Webhook;

use Webmozart\Assert\Assert;

class WebhookMessage
{
    private const ENTITY_BOT_COMMAND_TYPE = 'bot_command';

    public int $updateId = 0;
    public int $messageId = 0;
    public ?string $command = null;
    public int $chatId = 0;
    public ?string $username = null;
    public string $firstName = '';
    public string $text;

    public static function fromRequestData(array $data): WebhookMessage
    {
        Assert::keyExists($data, 'update_id');
        Assert::keyExists($data, 'message');
        Assert::keyExists($data['message'], 'message_id');
        Assert::keyExists($data['message'], 'from');
        Assert::keyExists($data['message'], 'chat');
        Assert::keyExists($data['message'], 'text');

        Assert::keyExists($data['message']['from'], 'is_bot');
        Assert::false($data['message']['from']['is_bot']);

        Assert::keyExists($data['message']['chat'], 'id');
        Assert::keyExists($data['message']['chat'], 'first_name');

        $webhookMessage = new WebhookMessage();
        $webhookMessage->updateId = $data['update_id'];
        $webhookMessage->messageId = $data['message']['message_id'];
        $webhookMessage->chatId = $data['message']['chat']['id'];
        $webhookMessage->firstName = $data['message']['chat']['first_name'];
        $webhookMessage->username = $data['message']['chat']['username'] ?? null;
        $webhookMessage->text = $data['message']['text'];


        $webhookMessage->command = WebhookMessage::parseCommand(
            $data['message']['text'],
            $data['message']['entities'] ?? []
        );

        return $webhookMessage;
    }

    public function isCommand(): bool
    {
        return $this->command !== null;
    }

    private static function parseCommand(string $text, array $entities): ?string
    {
        if (count($entities) === 0) {
            return null;
        }

        foreach ($entities as $entity) {
            if (isset($entity['type']) && $entity['type'] === self::ENTITY_BOT_COMMAND_TYPE) {
                $command = substr($text, $entity['offset'], $entity['length']);

                return substr($command, 1);
            }
        }

        return null;
    }
}
