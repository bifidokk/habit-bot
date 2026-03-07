<?php

declare(strict_types=1);

namespace App\Tests\Service\Command;

use App\Entity\User;
use App\Service\Command\MainMenuCommand;
use App\Service\Keyboard\MainMenuKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\UpdateType;

class MainMenuCommandTest extends TestCase
{
    private MainMenuCommand $command;

    private BotApiComplete&MockObject $bot;

    private MainMenuKeyboard&MockObject $mainMenuKeyboard;

    private TranslatorInterface&MockObject $translator;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->mainMenuKeyboard = $this->createMock(MainMenuKeyboard::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->command = new MainMenuCommand(
            $this->bot,
            $this->mainMenuKeyboard,
            $this->translator,
        );
    }

    public function testCanRunAlwaysFalse(): void
    {
        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), null));
    }

    public function testRunWithMessage(): void
    {
        $user = new User();
        $update = new UpdateType();
        $update->message = new \TgBotApi\BotApiBase\Type\MessageType();
        $update->message->chat = new \TgBotApi\BotApiBase\Type\ChatType();
        $update->message->chat->id = 123;

        $this->translator->method('trans')->willReturn('Main menu');

        $this->bot->expects($this->once())->method('sendMessage');

        $this->command->run($update, $user, null);
    }

    public function testRunWithCallbackQuery(): void
    {
        $user = new User();
        $update = new UpdateType();
        $update->message = null;
        $update->callbackQuery = new \TgBotApi\BotApiBase\Type\CallbackQueryType();
        $update->callbackQuery->message = new \TgBotApi\BotApiBase\Type\MessageType();
        $update->callbackQuery->message->chat = new \TgBotApi\BotApiBase\Type\ChatType();
        $update->callbackQuery->message->chat->id = 456;

        $this->translator->method('trans')->willReturn('Main menu');

        $this->bot->expects($this->once())->method('sendMessage');

        $this->command->run($update, $user, null);
    }
}
