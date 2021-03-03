<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\Webhook\WebhookMessage;
use PHPUnit\Framework\TestCase;

class WebhookMessageTest extends TestCase
{
    public function testItCreatesWebhookMessage(): void
    {
        $json = '{"update_id": 566689192,"message": {"message_id": 11,"from": {"id": 17685207,"is_bot": false,"first_name": "Firstname","username": "username","language_code": "ru"},"chat": {"id": 1346207,"first_name": "Firstname","username": "username","type": "private"},"date": 1614800525,"text": "/start", "entities": [{"offset": 0,"length": 6,"type": "bot_command"}]}}';
        $webhookMessage = WebhookMessage::fromRequestData(
            json_decode($json, true, 512, JSON_THROW_ON_ERROR)
        );


        $this->assertTrue($webhookMessage->isCommand());
    }
}
