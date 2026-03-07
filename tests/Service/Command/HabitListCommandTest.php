<?php

declare(strict_types=1);

namespace App\Tests\Service\Command;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\HabitListCommand;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\HabitViewInlineKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\UpdateType;

class HabitListCommandTest extends TestCase
{
    private HabitListCommand $command;

    private BotApiComplete&MockObject $bot;

    private HabitViewInlineKeyboard&MockObject $habitViewInlineKeyboard;

    private HabitService&MockObject $habitService;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->habitViewInlineKeyboard = $this->createMock(HabitViewInlineKeyboard::class);
        $this->habitService = $this->createMock(HabitService::class);

        $this->command = new HabitListCommand(
            $this->bot,
            $this->habitViewInlineKeyboard,
            $this->habitService,
        );
    }

    public function testCanRunWithListCallback(): void
    {
        $update = new UpdateType();
        $user = new User();
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitList;

        $this->assertTrue($this->command->canRun($update, $user, $callback));
    }

    public function testRunWithNoHabits(): void
    {
        $user = new User();
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitList;
        $callback->parameters = [
            'page' => '0',
        ];

        $update = new UpdateType();

        $this->habitService->method('getUserHabits')->willReturn([]);
        $this->bot->expects($this->never())->method('editMessageText');

        $this->command->run($update, $user, $callback);
    }

    public function testRunWithSingleHabit(): void
    {
        $user = new User();
        $habit = new Habit();
        $habit->setUser($user);

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitList;
        $callback->parameters = [
            'page' => '0',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getUserHabits')->willReturn([$habit]);
        $this->habitService->method('getHabitPreviewText')->willReturn('Preview');

        $this->habitViewInlineKeyboard->expects($this->once())
            ->method('generate')
            ->with($habit, 0, false)
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');

        $this->command->run($update, $user, $callback);
    }

    public function testRunWithMultipleHabitsShowsNext(): void
    {
        $user = new User();
        $habit1 = new Habit();
        $habit2 = new Habit();

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitList;
        $callback->parameters = [
            'page' => '0',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getUserHabits')->willReturn([$habit1, $habit2]);
        $this->habitService->method('getHabitPreviewText')->willReturn('Preview');

        $this->habitViewInlineKeyboard->expects($this->once())
            ->method('generate')
            ->with($habit1, 0, true)
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');

        $this->command->run($update, $user, $callback);
    }

    public function testRunOutOfRangePage(): void
    {
        $user = new User();
        $habit = new Habit();

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitList;
        $callback->parameters = [
            'page' => '5',
        ];

        $update = new UpdateType();

        $this->habitService->method('getUserHabits')->willReturn([$habit]);
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
