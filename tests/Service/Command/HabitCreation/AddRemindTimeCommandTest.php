<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\HabitCreation\AddRemindTimeCommand;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\HabitInlineKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddRemindTimeCommandTest extends TestCase
{
    private AddRemindTimeCommand $command;

    private BotApiComplete&MockObject $bot;

    private HabitService&MockObject $habitService;

    private TranslatorInterface&MockObject $translator;

    private HabitInlineKeyboard&MockObject $habitInlineKeyboard;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->habitService = $this->createMock(HabitService::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->habitInlineKeyboard = $this->createMock(HabitInlineKeyboard::class);

        $this->command = new AddRemindTimeCommand(
            $this->bot,
            $this->habitService,
            $this->translator,
            $this->habitInlineKeyboard,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetHabitRemindTime;

        $this->assertTrue($this->command->canRun(new UpdateType(), new User(), $callback));
    }

    public function testCanRunWithNull(): void
    {
        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), null));
    }

    public function testRunWithNullCallback(): void
    {
        $update = new UpdateType();
        $user = new User();

        $this->bot->expects($this->never())->method('editMessageText');

        $this->command->run($update, $user, null);
    }

    public function testRunWithValidTime(): void
    {
        $user = new User();
        $habit = new Habit();

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetHabitRemindTime;
        $callback->parameters = [
            'id' => 'some-id',
            'time' => '09:00',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);
        $this->habitService->expects($this->once())->method('save')->with($habit);

        $this->translator->method('trans')->willReturn('Creation menu');
        $this->habitInlineKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');

        $this->command->run($update, $user, $callback);
    }

    public function testRunWithInvalidTime(): void
    {
        $user = new User();
        $habit = new Habit();

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetHabitRemindTime;
        $callback->parameters = [
            'id' => 'some-id',
            'time' => 'not-a-time',
        ];

        $update = new UpdateType();

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);
        $this->habitService->expects($this->never())->method('save');
        $this->bot->expects($this->never())->method('editMessageText');

        $this->command->run($update, $user, $callback);
    }

    private function createCallbackUpdate(int $chatId, int $messageId): UpdateType
    {
        $update = new UpdateType();
        $update->callbackQuery = new \TgBotApi\BotApiBase\Type\CallbackQueryType();
        $update->callbackQuery->message = new \TgBotApi\BotApiBase\Type\MessageType();
        $update->callbackQuery->message->chat = new \TgBotApi\BotApiBase\Type\ChatType();
        $update->callbackQuery->message->chat->id = $chatId;
        $update->callbackQuery->message->messageId = $messageId;

        return $update;
    }
}
