<?php

declare(strict_types=1);

namespace App\Tests\Functional\SingleCommand\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Command\HabitCreation\AddRemindDayCommand;
use App\Service\Command\HabitCreation\AddDescriptionCommand;
use App\Service\Command\HabitCreation\StartCommand;
use App\Service\Habit\CreationHabitState;
use App\Service\Habit\HabitState;
use App\Service\Keyboard\HabitRemindDayKeyboard;
use App\Service\Keyboard\NewHabitKeyboard;
use App\Service\User\UserState;
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
                'replyMarkup' => HabitRemindDayKeyboard::generate(0),
            ]);

        $this->botApiCompleteMock->expects($this->once())
            ->method('sendMessage')
            ->withConsecutive(
                [$methodAddRemindDay]
            );

        $this->sendRequest(WebhookDataFactory::getHabitCreationAddTitleCommandData());

        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy([
            'telegramId' => 1,
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(UserState::NEW_HABIT, $user->getState());

        $habit = $user->getDraftHabit();

        $this->assertInstanceOf(Habit::class, $habit);
        $this->assertEquals('This a new habit', $habit->getDescription());
        $this->assertEquals(HabitState::DRAFT, $habit->getState());
        $this->assertEquals(CreationHabitState::TITLE_ADDED, $habit->getCreationState());
    }

    public function testAddTitleCommandWithInvalidDescription(): void
    {
        $this->prepareState();

        $errorMessageMethod = SendMessageMethod::create(
            1,
            sprintf(AddDescriptionCommand::ERROR_TEMPLATE_TEXT, AddDescriptionCommand::ERROR_DESCRIPTION_TEXT)
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
