<?php

declare(strict_types=1);

namespace App\Tests\Functional\SingleCommand;

use App\Service\Command\MainMenuCommand;
use App\Service\Command\StartCommand;
use App\Service\Keyboard\MainMenuKeyboard;
use App\Tests\Functional\CommandTest;
use TgBotApi\BotApiBase\Method\SendMessageMethod;

class StartCommandTest extends CommandTest
{
    public function testStartCommand(): void
    {
        $chatId = 1;
        $methodStart = SendMessageMethod::create(
            $chatId,
            StartCommand::COMMAND_RESPONSE_TEXT
        );

        $methodMainMenu = SendMessageMethod::create(
            $chatId,
            MainMenuCommand::COMMAND_RESPONSE_TEXT, [
                'replyMarkup' => MainMenuKeyboard::generate(),
            ]);

        $this->botApiCompleteMock->expects($this->exactly(2))
            ->method('sendMessage')
            ->withConsecutive(
                [$methodStart],
                [$methodMainMenu]
            );

        $this->sendRequest($this->getContent());
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
