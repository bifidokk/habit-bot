<?php

declare(strict_types=1);

namespace App\Tests\Functional\SingleCommand\HabitCreation;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Command\HabitCreation\AddRemindDayCommand;
use App\Service\Keyboard\HabitPeriodMenuKeyboard;
use App\Tests\Functional\CommandTest;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UserType;

class AddTitleCommandTest extends CommandTest
{
    public function testAddTitleCommand(): void
    {
        $userType = new UserType();
        $userType->id = 1;
        $userType->firstName = 'John';
        $userType->username = 'johndoe';
        $userType->languageCode = 'ru';

        $user = User::createFromUserType($userType);
        $userRepository = static::$container->get(UserRepository::class);
        $userRepository->save($user);

        $startContent = StartCommandTest::getContent();
        $this->sendRequest($startContent);

        $methodAddRemindDay = SendMessageMethod::create(
            1,
            AddRemindDayCommand::COMMAND_RESPONSE_TEXT, [
                'replyMarkup' => HabitPeriodMenuKeyboard::generate(0),
            ]);

        $this->botApiCompleteMock->expects($this->once())
            ->method('sendMessage')
            ->withConsecutive(
                [$methodAddRemindDay]
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
        "text": "This a new habit"
    }
}';
    }
}
