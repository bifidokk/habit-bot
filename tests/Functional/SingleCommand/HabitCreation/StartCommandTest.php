<?php

declare(strict_types=1);

namespace App\Tests\Functional\SingleCommand\HabitCreation;

use App\Service\Command\HabitCreation\StartCommand;
use App\Service\Keyboard\NewHabitKeyboard;
use App\Tests\Functional\CommandTest;
use TgBotApi\BotApiBase\Method\SendMessageMethod;

class StartCommandTest extends CommandTest
{
    public function testStartCommand(): void
    {
        $chatId = 1;
        $methodStart = SendMessageMethod::create(
            $chatId,
            StartCommand::COMMAND_RESPONSE_TEXT, [
                'replyMarkup' => NewHabitKeyboard::generate(),
            ]);

        $this->botApiCompleteMock->expects($this->once())
            ->method('sendMessage')
            ->withConsecutive(
                [$methodStart]
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
        "date": 1617729513,
        "text": "Add a new habit"
    }
}';
    }
}
