<?php

declare(strict_types=1);

namespace App\Tests\Service\Command;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\HabitRemoveConfirmCommand;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\HabitConfirmRemoveInlineKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\UpdateType;

class HabitRemoveConfirmCommandTest extends TestCase
{
    private HabitRemoveConfirmCommand $command;

    private BotApiComplete&MockObject $bot;

    private HabitConfirmRemoveInlineKeyboard&MockObject $habitConfirmRemoveInlineKeyboard;

    private HabitService&MockObject $habitService;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->habitConfirmRemoveInlineKeyboard = $this->createMock(HabitConfirmRemoveInlineKeyboard::class);
        $this->habitService = $this->createMock(HabitService::class);

        $this->command = new HabitRemoveConfirmCommand(
            $this->bot,
            $this->habitConfirmRemoveInlineKeyboard,
            $this->habitService,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitRemoveConfirm;

        $this->assertTrue($this->command->canRun(new UpdateType(), new User(), $callback));
    }

    public function testCanRunWithNull(): void
    {
        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), null));
    }

    public function testRunEditsMessageWithConfirmKeyboard(): void
    {
        $user = new User();
        $habit = new Habit();

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitRemoveConfirm;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitById')->willReturn($habit);
        $this->habitService->method('getHabitRemoveConfirmText')->willReturn('Are you sure?');
        $this->habitConfirmRemoveInlineKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');

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
