<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\HabitCreation\AddRemindDayCommand;
use App\Service\Habit\HabitService;
use App\Service\Habit\RemindService;
use App\Service\Keyboard\HabitInlineKeyboard;
use App\Service\Keyboard\HabitRemindDayInlineKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddRemindDayCommandTest extends TestCase
{
    private AddRemindDayCommand $command;

    private BotApiComplete&MockObject $bot;

    private RemindService&MockObject $remindService;

    private HabitService&MockObject $habitService;

    private HabitRemindDayInlineKeyboard&MockObject $habitRemindDayInlineKeyboard;

    private TranslatorInterface&MockObject $translator;

    private HabitInlineKeyboard&MockObject $habitInlineKeyboard;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->remindService = $this->createMock(RemindService::class);
        $this->habitService = $this->createMock(HabitService::class);
        $this->habitRemindDayInlineKeyboard = $this->createMock(HabitRemindDayInlineKeyboard::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->habitInlineKeyboard = $this->createMock(HabitInlineKeyboard::class);

        $this->command = new AddRemindDayCommand(
            $this->bot,
            $this->remindService,
            $this->habitService,
            $this->habitRemindDayInlineKeyboard,
            $this->translator,
            $this->habitInlineKeyboard,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetHabitRemindDay;

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

    public function testRunToggleDay(): void
    {
        $user = new User();
        $habit = $this->createHabitWithId();

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetHabitRemindDay;
        $callback->parameters = [
            'id' => $habit->getId()->toRfc4122(),
            'day' => 'Mon',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);

        $this->remindService->expects($this->once())
            ->method('toggleDay')
            ->with($habit, 0);

        $this->translator->method('trans')->willReturn('Choose days');
        $this->habitRemindDayInlineKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');

        $this->command->run($update, $user, $callback);
    }

    public function testRunMarkAll(): void
    {
        $user = new User();
        $habit = $this->createHabitWithId();

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetHabitRemindDay;
        $callback->parameters = [
            'id' => $habit->getId()->toRfc4122(),
            'day' => 'all',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);

        $this->remindService->expects($this->once())
            ->method('markAll')
            ->with($habit);

        $this->translator->method('trans')->willReturn('Choose days');
        $this->habitRemindDayInlineKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');

        $this->command->run($update, $user, $callback);
    }

    public function testRunNextWithDaysSet(): void
    {
        $user = new User();
        $habit = $this->createHabitWithId();
        $habit->setRemindWeekDays(127);

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetHabitRemindDay;
        $callback->parameters = [
            'id' => $habit->getId()->toRfc4122(),
            'day' => 'next',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);

        $this->translator->method('trans')->willReturn('Creation menu');
        $this->habitInlineKeyboard->expects($this->once())
            ->method('generate')
            ->with($habit)
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');

        $this->command->run($update, $user, $callback);
    }

    public function testRunNextWithNoDays(): void
    {
        $user = new User();
        $habit = $this->createHabitWithId();
        $habit->setRemindWeekDays(0);

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetHabitRemindDay;
        $callback->parameters = [
            'id' => $habit->getId()->toRfc4122(),
            'day' => 'next',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);

        $this->translator->method('trans')->willReturn('Choose days');
        $this->habitRemindDayInlineKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        // Should still call editMessageText via updateKeyboard
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

    private function createHabitWithId(): Habit
    {
        $habit = new Habit();
        $habit->setUser(new User());
        $habit->setRemindWeekDays(0);

        $reflection = new \ReflectionClass($habit);
        $property = $reflection->getProperty('id');
        $property->setValue($habit, Uuid::v4());

        return $habit;
    }
}
