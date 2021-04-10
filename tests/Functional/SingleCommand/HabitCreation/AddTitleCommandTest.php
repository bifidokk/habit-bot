<?php

declare(strict_types=1);

namespace App\Tests\Functional\SingleCommand\HabitCreation;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Command\HabitCreation\AddRemindDayCommand;
use App\Service\Command\HabitCreation\AddTitleCommand;
use App\Service\Command\HabitCreation\StartCommand;
use App\Service\Keyboard\HabitPeriodMenuKeyboard;
use App\Service\Keyboard\NewHabitKeyboard;
use App\Tests\Functional\CommandTest;
use App\Tests\Functional\WebhookDataFactory;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UserType;

class AddTitleCommandTest extends CommandTest
{
    public function testAddTitleCommand(): void
    {
        $this->prepareState();

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

        $this->sendRequest(WebhookDataFactory::getHabitCreationAddTitleCommandData());
    }

    public function testAddTitleCommandWithInvalidDescription(): void
    {
        $this->prepareState();

        $errorMessageMethod = SendMessageMethod::create(
            1,
            sprintf(AddTitleCommand::ERROR_TEMPLATE_TEXT, AddTitleCommand::ERROR_DESCRIPTION_TEXT)
        );

        $retryMessageMethod = SendMessageMethod::create(
            1,
            StartCommand::COMMAND_RESPONSE_TEXT, [
                'replyMarkup' => NewHabitKeyboard::generate(),
            ]
        );

        $this->botApiCompleteMock->expects($this->exactly(2))
            ->method('sendMessage')
            ->withConsecutive(
                [$errorMessageMethod],
                [$retryMessageMethod]
            );

        $this->sendRequest(WebhookDataFactory::getEmptyHabitCreationAddTitleCommandData());
    }

    private function prepareState(): void
    {
        $userType = new UserType();
        $userType->id = 1;
        $userType->firstName = 'John';
        $userType->username = 'johndoe';
        $userType->languageCode = 'ru';

        $user = User::createFromUserType($userType);
        $userRepository = static::$container->get(UserRepository::class);
        $userRepository->save($user);

        $this->sendRequest(WebhookDataFactory::getHabitCreationStartCommandData());
    }
}
