<?php

declare(strict_types=1);

namespace App\Tests\Service\Command;

use App\Entity\User;
use App\Service\Command\HabitMenuCommand;
use App\Service\Keyboard\HabitMenuInlineKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\UpdateType;

class HabitMenuCommandTest extends TestCase
{
    private HabitMenuCommand $command;

    private BotApiComplete&MockObject $bot;

    private HabitMenuInlineKeyboard&MockObject $habitMenuInlineKeyboard;

    private TranslatorInterface&MockObject $translator;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->habitMenuInlineKeyboard = $this->createMock(HabitMenuInlineKeyboard::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->command = new HabitMenuCommand(
            $this->bot,
            $this->habitMenuInlineKeyboard,
            $this->translator,
        );
    }

    public function testCanRunWithMatchingMessage(): void
    {
        $this->translator->method('trans')->with('habits')->willReturn('Habits');

        $update = new UpdateType();
        $update->message = new \TgBotApi\BotApiBase\Type\MessageType();
        $update->message->text = '⏰ Habits';

        $this->assertTrue($this->command->canRun($update, new User(), null));
    }

    public function testCanRunWithNonMatchingMessage(): void
    {
        $this->translator->method('trans')->with('habits')->willReturn('Habits');

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

    public function testRunSendsMessageWithHabitMenu(): void
    {
        $user = new User();

        $update = new UpdateType();
        $update->message = new \TgBotApi\BotApiBase\Type\MessageType();
        $update->message->text = '⏰ Habits';
        $update->message->chat = new \TgBotApi\BotApiBase\Type\ChatType();
        $update->message->chat->id = 123;

        $this->habitMenuInlineKeyboard->expects($this->once())
            ->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('sendMessage');

        $this->command->run($update, $user, null);
    }
}
