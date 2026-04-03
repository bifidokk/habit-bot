<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\HabitCreation\BackToRemindTimeCommand;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\HabitRemindTimeInlineKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\UpdateType;

class BackToRemindTimeCommandTest extends TestCase
{
    private BackToRemindTimeCommand $command;

    private BotApiComplete&MockObject $bot;

    private HabitService&MockObject $habitService;

    private HabitRemindTimeInlineKeyboard&MockObject $habitRemindTimeInlineKeyboard;

    private TranslatorInterface&MockObject $translator;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->habitService = $this->createMock(HabitService::class);
        $this->habitRemindTimeInlineKeyboard = $this->createMock(HabitRemindTimeInlineKeyboard::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->command = new BackToRemindTimeCommand(
            $this->bot,
            $this->habitService,
            $this->habitRemindTimeInlineKeyboard,
            $this->translator,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::BackToRemindTime;

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

    public function testRun(): void
    {
        $user = new User();
        $habit = $this->createHabitWithId();

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::BackToRemindTime;
        $callback->parameters = ['id' => $habit->getId()->toRfc4122()];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);

        $this->translator->method('trans')->willReturn('Choose time');
        $this->habitRemindTimeInlineKeyboard->expects($this->once())
            ->method('generate')
            ->with($habit->getId()->toRfc4122())
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');

        $this->command->run($update, $user, $callback);
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