<?php

declare(strict_types=1);

namespace App\Tests\Functional\SingleCommand\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Command\HabitCreation\AddRemindTimeCommand;
use App\Service\Command\MainMenuCommand;
use App\Service\Habit\CreationHabitState;
use App\Service\Habit\HabitState;
use App\Service\Keyboard\HabitRemindTimeKeyboard;
use App\Service\Keyboard\MainMenuKeyboard;
use App\Service\User\UserState;
use App\Tests\Functional\CommandTest;
use App\Tests\Functional\WebhookDataFactory;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UserType;

class AddRemindTimeCommandTest extends CommandTest
{
    public function testAddRemindTimeCommand(): void
    {
        $this->prepareState();

        $sendMethod = SendMessageMethod::create(1, AddRemindTimeCommand::COMMAND_SUCCESS_TEXT);

        $mainMenuMethod = SendMessageMethod::create(
            1,
            MainMenuCommand::COMMAND_RESPONSE_TEXT, [
                'replyMarkup' => MainMenuKeyboard::generate(),
            ]);

        $this->botApiCompleteMock->expects($this->exactly(2))
            ->method('sendMessage')
            ->withConsecutive(
                [$sendMethod],
                [$mainMenuMethod]
            );

        $this->sendRequest(WebhookDataFactory::getHabitCreationAddRemindTimeCommandData());

        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy([
            'telegramId' => 1,
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(UserState::START, $user->getState());

        $habit = $user->getLastPublishedHabit();
        $this->assertEquals(HabitState::PUBLISHED, $habit->getState());
        $this->assertEquals(CreationHabitState::TIME_ADDED, $habit->getCreationState());

        $this->assertInstanceOf(Habit::class, $habit);
        $remindAt = new \DateTimeImmutable('4:20');
        $this->assertEquals($remindAt, $habit->getRemindAt());
    }

    public function testAddInvalidRemindTimeCommand(): void
    {
        $this->prepareState();

        $sendMethod = SendMessageMethod::create(
            1,
            AddRemindTimeCommand::COMMAND_RESPONSE_TEXT, [
            'replyMarkup' => HabitRemindTimeKeyboard::generate(),
        ]);

        $this->botApiCompleteMock->expects($this->once())
            ->method('sendMessage')
            ->withConsecutive(
                [$sendMethod]
            );

        $this->sendRequest(WebhookDataFactory::getHabitCreationAddInvalidRemindTimeCommandData());
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
        $this->sendRequest(WebhookDataFactory::getHabitCreationAddRemindDayCommandData());
        $this->sendRequest(WebhookDataFactory::getHabitCreationAddRemindDayNextCommandData());
    }
}
