<?php

declare(strict_types=1);

namespace App\Tests\Functional\SingleCommand\HabitCreation;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Command\HabitCreation\StartCommand;
use App\Service\Keyboard\NewHabitKeyboard;
use App\Service\User\UserState;
use App\Tests\Functional\CommandTest;
use App\Tests\Functional\WebhookDataFactory;
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

        $this->sendRequest(WebhookDataFactory::getHabitCreationStartCommandData());

        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy([
            'telegramId' => 1,
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(UserState::NEW_HABIT, $user->getState());
    }
}
