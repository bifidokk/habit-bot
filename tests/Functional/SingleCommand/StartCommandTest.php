<?php

declare(strict_types=1);

namespace App\Tests\Functional\SingleCommand;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Keyboard\MainMenuKeyboard;
use App\Tests\Functional\Command;
use App\Tests\Functional\WebhookDataFactory;
use App\Translator\NoTranslator;
use TgBotApi\BotApiBase\Method\SendMessageMethod;

class StartCommandTest extends Command
{
    public function testStartCommand(): void
    {
        $translator = new NoTranslator();
        $mainMenuKeyboard = new MainMenuKeyboard($translator);

        $chatId = 1;
        $methodStart = SendMessageMethod::create(
            $chatId,
            $translator->trans('command.response.start'),
            [
                'replyMarkup' => $mainMenuKeyboard->generate(),
            ]
        );

        $this->botApiCompleteMock->expects($this->exactly(1))
            ->method('sendMessage')
            ->withConsecutive(
                [$methodStart]
            );

        $this->sendRequest(WebhookDataFactory::getStartCommandData());

        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByTelegramId(1);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('johndoe', $user->getUsername());
    }
}
