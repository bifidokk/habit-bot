<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Service\Keyboard\MainMenuKeyboard;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TgBotApi\BotApiBase\Method\SendMessageMethod;

class StartCommandTest extends WebTestCase
{
    public function testStartCommand(): void
    {
        $methodStart = SendMessageMethod::create(
            '1',
            'Hey there! You can add a new habit here'
        );

        $methodMainMenu = SendMessageMethod::create(
            '1',
            'You are in the main menu', [
            'replyMarkup' => MainMenuKeyboard::generate(),
        ]);

        $botApiComplete = $this->createMock(\TgBotApi\BotApiBase\BotApiComplete::class);

        $botApiComplete->expects($this->exactly(2))
            ->method('sendMessage')
            ->withConsecutive(
                [$methodStart],
                [$methodMainMenu]
            );

        $client = static::createClient();
        $client->getContainer()->set('TgBotApi\BotApiBase\BotApiComplete', $botApiComplete);
        $client->request('POST', sprintf('/webhook/%s', getenv('TG_BOT_WEBHOOK_TOKEN')), [], [], [], $this->getContent());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    private function getContent(): string
    {
        return '{
    "update_id": 1,
    "message": {
        "message_id": 1,
        "from": {
            "id": 1,
            "is_bot": false,
            "first_name": "John",
            "username": "johndoe",
            "language_code": "ru"
        },
        "chat": {
            "id": 1,
            "first_name": "John",
            "username": "johndoe",
            "type": "private"
        },
        "date": 1617554366,
        "text": "/start",
        "entities": [
            {
                "offset": 0,
                "length": 6,
                "type": "bot_command"
            }
        ]
    }
}';
    }
}
