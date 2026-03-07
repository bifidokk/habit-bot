<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\HabitCreation\StartCommand;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\HabitInlineKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\UpdateType;

class StartCommandTest extends TestCase
{
    private StartCommand $command;

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

        $this->command = new StartCommand(
            $this->bot,
            $this->habitService,
            $this->translator,
            $this->habitInlineKeyboard,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitForm;

        $this->assertTrue($this->command->canRun(new UpdateType(), new User(), $callback));
    }

    public function testCanRunWithNull(): void
    {
        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), null));
    }

    public function testRunWithExistingDraftHabit(): void
    {
        $user = new User();
        $habit = new Habit();

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitForm;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);
        $this->habitService->expects($this->never())->method('removeUserDraftHabits');
        $this->habitService->expects($this->never())->method('createDraftHabit');

        $this->translator->method('trans')->willReturn('Creation');
        $this->habitInlineKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');

        $this->command->run($update, $user, $callback);
    }

    public function testRunWithoutIdCreatesNewDraft(): void
    {
        $user = new User();
        $habit = new Habit();

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitForm;
        $callback->parameters = [];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->expects($this->once())->method('removeUserDraftHabits')->with($user);
        $this->habitService->expects($this->once())->method('createDraftHabit')->with($user)->willReturn($habit);

        $this->translator->method('trans')->willReturn('Creation');
        $this->habitInlineKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');

        $this->command->run($update, $user, $callback);
    }

    public function testRunWithNullCallback(): void
    {
        $user = new User();
        $habit = new Habit();

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->expects($this->once())->method('removeUserDraftHabits')->with($user);
        $this->habitService->expects($this->once())->method('createDraftHabit')->with($user)->willReturn($habit);

        $this->translator->method('trans')->willReturn('Creation');
        $this->habitInlineKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');

        $this->command->run($update, $user, null);
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
