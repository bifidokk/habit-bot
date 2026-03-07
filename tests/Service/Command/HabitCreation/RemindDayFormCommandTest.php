<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\HabitCreation\RemindDayFormCommand;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\HabitRemindDayInlineKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\UpdateType;

class RemindDayFormCommandTest extends TestCase
{
    private RemindDayFormCommand $command;

    private BotApiComplete&MockObject $bot;

    private HabitService&MockObject $habitService;

    private HabitRemindDayInlineKeyboard&MockObject $habitRemindDayInlineKeyboard;

    private TranslatorInterface&MockObject $translator;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->habitService = $this->createMock(HabitService::class);
        $this->habitRemindDayInlineKeyboard = $this->createMock(HabitRemindDayInlineKeyboard::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->command = new RemindDayFormCommand(
            $this->bot,
            $this->habitService,
            $this->habitRemindDayInlineKeyboard,
            $this->translator,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitRemindDayForm;

        $this->assertTrue($this->command->canRun(new UpdateType(), new User(), $callback));
    }

    public function testCanRunWithNull(): void
    {
        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), null));
    }

    public function testRunEditsMessageWithDayKeyboard(): void
    {
        $user = new User();
        $habit = new Habit();
        $habit->setRemindWeekDays(64);
        $reflection = new \ReflectionClass($habit);
        $property = $reflection->getProperty('id');
        $property->setValue($habit, Uuid::v4());

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitRemindDayForm;
        $callback->parameters = [
            'id' => $habit->getId()->toRfc4122(),
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);
        $this->translator->method('trans')->willReturn('Choose days');
        $this->habitRemindDayInlineKeyboard->method('generate')
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
