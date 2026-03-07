<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\Settings;

use App\Entity\User;
use App\Service\Command\Settings\SettingsCommand;
use App\Service\Keyboard\SettingsInlineKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\UpdateType;

class SettingsCommandTest extends TestCase
{
    private SettingsCommand $command;

    private BotApiComplete&MockObject $bot;

    private SettingsInlineKeyboard&MockObject $settingsInlineKeyboard;

    private TranslatorInterface&MockObject $translator;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->settingsInlineKeyboard = $this->createMock(SettingsInlineKeyboard::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->command = new SettingsCommand(
            $this->bot,
            $this->settingsInlineKeyboard,
            $this->translator,
        );
    }

    public function testCanRunWithMatchingMessage(): void
    {
        $this->translator->method('trans')->with('settings')->willReturn('Settings');

        $update = new UpdateType();
        $update->message = new \TgBotApi\BotApiBase\Type\MessageType();
        $update->message->text = '🛠️ Settings';

        $this->assertTrue($this->command->canRun($update, new User(), null));
    }

    public function testCanRunWithNonMatchingMessage(): void
    {
        $this->translator->method('trans')->with('settings')->willReturn('Settings');

        $update = new UpdateType();
        $update->message = new \TgBotApi\BotApiBase\Type\MessageType();
        $update->message->text = 'Something else';

        $this->assertFalse($this->command->canRun($update, new User(), null));
    }

    public function testCanRunWithNullMessage(): void
    {
        $update = new UpdateType();
        $update->message = null;

        $this->assertFalse($this->command->canRun($update, new User(), null));
    }

    public function testRunSendsSettingsMenu(): void
    {
        $user = new User();
        $update = new UpdateType();
        $update->message = new \TgBotApi\BotApiBase\Type\MessageType();
        $update->message->text = '🛠️ Settings';
        $update->message->chat = new \TgBotApi\BotApiBase\Type\ChatType();
        $update->message->chat->id = 123;

        $this->settingsInlineKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('sendMessage');

        $this->command->run($update, $user, null);
    }
}
