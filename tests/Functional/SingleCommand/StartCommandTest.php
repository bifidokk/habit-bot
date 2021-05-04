<?php

declare(strict_types=1);

namespace App\Tests\Functional\SingleCommand;

use App\Service\Command\MainMenuCommand;
use App\Service\Command\StartCommand;
use App\Service\Keyboard\MainMenuKeyboard;
use App\Tests\Functional\CommandTest;
use App\Tests\Functional\WebhookDataFactory;
use App\Translator\NoTranslator;
use TgBotApi\BotApiBase\Method\SendMessageMethod;

class StartCommandTest extends CommandTest
{
    public function testStartCommand(): void
    {
        $translator = new NoTranslator();
        $mainMenuKeyboard = new MainMenuKeyboard($translator);

        $chatId = 1;
        $methodStart = SendMessageMethod::create(
            $chatId,
            StartCommand::COMMAND_RESPONSE_TEXT
        );

        $methodMainMenu = SendMessageMethod::create(
            $chatId,
            MainMenuCommand::COMMAND_RESPONSE_TEXT, [
                'replyMarkup' => $mainMenuKeyboard->generate(),
            ]);

        $this->botApiCompleteMock->expects($this->exactly(2))
            ->method('sendMessage')
            ->withConsecutive(
                [$methodStart],
                [$methodMainMenu]
            );

        $this->sendRequest(WebhookDataFactory::getStartCommandData());
    }
}
