<?php

declare(strict_types=1);

namespace App\Tests\Functional\SingleCommand\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Command\HabitCreation\AddRemindDayCommand;
use App\Service\Habit\CreationHabitState;
use App\Service\Keyboard\HabitPeriodMenuKeyboard;
use App\Service\User\UserState;
use App\Tests\Functional\CommandTest;
use App\Tests\Functional\WebhookDataFactory;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UserType;

class AddRemindDayCommandTest extends CommandTest
{
    public function testAddRemindDayCommand(): void
    {
        $this->prepareState();

        $methodAddRemindDay = SendMessageMethod::create(
            1,
            AddRemindDayCommand::COMMAND_RESPONSE_TEXT, [
                'replyMarkup' => HabitPeriodMenuKeyboard::generate(64),
            ]);

        $this->botApiCompleteMock->expects($this->once())
            ->method('sendMessage')
            ->withConsecutive(
                [$methodAddRemindDay]
            );

        $this->sendRequest(WebhookDataFactory::getHabitCreationAddRemindDayCommandData());

        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy([
            'telegramId' => 1,
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(UserState::NEW_HABIT, $user->getState());

        $habit = $user->getDraftHabit();

        $this->assertInstanceOf(Habit::class, $habit);
        $this->assertEquals(64, $habit->getRemindWeekDays());
    }

    public function testChooseAllAddRemindDayCommand(): void
    {
        $this->prepareState();

        $methodAddRemindDay = SendMessageMethod::create(
            1,
            AddRemindDayCommand::COMMAND_RESPONSE_TEXT, [
                'replyMarkup' => HabitPeriodMenuKeyboard::generate(127),
            ]);

        $this->botApiCompleteMock->expects($this->once())
            ->method('sendMessage')
            ->withConsecutive(
                [$methodAddRemindDay]
            );

        $this->sendRequest(WebhookDataFactory::getHabitCreationAddAllRemindDaysCommandData());

        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy([
            'telegramId' => 1,
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(UserState::NEW_HABIT, $user->getState());

        $habit = $user->getDraftHabit();

        $this->assertInstanceOf(Habit::class, $habit);
        $this->assertEquals(127, $habit->getRemindWeekDays());
    }

    public function testAddRemindDayNextCommand(): void
    {
        $this->prepareState();

        $this->sendRequest(WebhookDataFactory::getHabitCreationAddRemindDayCommandData());
        $this->sendRequest(WebhookDataFactory::getHabitCreationAddRemindDayNextCommandData());

        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy([
            'telegramId' => 1,
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(UserState::NEW_HABIT, $user->getState());

        $habit = $user->getDraftHabit();

        $this->assertInstanceOf(Habit::class, $habit);
        $this->assertEquals(64, $habit->getRemindWeekDays());
        $this->assertEquals(CreationHabitState::PERIOD_ADDED, $habit->getCreationState());
    }

    public function testAddRemindDayNextWithoutSavedCommand(): void
    {
        $this->prepareState();

        $messageMethod = SendMessageMethod::create(
            1,
            AddRemindDayCommand::COMMAND_RESPONSE_NEXT_TEXT, [
                'replyMarkup' => HabitPeriodMenuKeyboard::generate(0),
            ]);

        $this->botApiCompleteMock->expects($this->once())
            ->method('sendMessage')
            ->withConsecutive(
                [$messageMethod]
            );

        $this->sendRequest(WebhookDataFactory::getHabitCreationAddRemindDayNextCommandData());
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
        $this->sendRequest(WebhookDataFactory::getHabitCreationAddTitleCommandData());
    }
}
