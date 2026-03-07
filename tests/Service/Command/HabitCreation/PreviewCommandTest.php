<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\HabitCreation\PreviewCommand;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\HabitPreviewInlineKeyboard;
use App\Service\Message\SendMessageMethodFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;
use TgBotApi\BotApiBase\Type\UpdateType;

class PreviewCommandTest extends TestCase
{
    private PreviewCommand $command;

    private BotApiComplete&MockObject $bot;

    private HabitService&MockObject $habitService;

    private HabitPreviewInlineKeyboard&MockObject $habitPreviewInlineKeyboard;

    private SendMessageMethodFactory&MockObject $sendMessageMethodFactory;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->habitService = $this->createMock(HabitService::class);
        $this->habitPreviewInlineKeyboard = $this->createMock(HabitPreviewInlineKeyboard::class);
        $this->sendMessageMethodFactory = $this->createMock(SendMessageMethodFactory::class);

        $this->command = new PreviewCommand(
            $this->bot,
            $this->habitService,
            $this->habitPreviewInlineKeyboard,
            $this->sendMessageMethodFactory,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitPreview;

        $this->assertTrue($this->command->canRun(new UpdateType(), new User(), $callback));
    }

    public function testCanRunWithNull(): void
    {
        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), null));
    }

    public function testRunReadyForPublishingShowsPreview(): void
    {
        $user = new User();
        $habit = new Habit();
        $habit->setDescription('Test');
        $habit->setRemindWeekDays(127);
        $habit->setRemindAt(new \DateTimeImmutable('09:00'));

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitPreview;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);
        $this->habitService->method('getHabitPreviewText')->willReturn('Preview text');
        $this->habitPreviewInlineKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('editMessageText');
        $this->bot->expects($this->never())->method('sendMessage');

        $this->command->run($update, $user, $callback);
    }

    public function testRunNotReadyFallsBackToMenu(): void
    {
        $user = new User();
        $habit = new Habit();

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitPreview;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);

        $this->sendMessageMethodFactory->expects($this->once())
            ->method('createHabitMenuMethod')
            ->with(123, $habit)
            ->willReturn(SendMessageMethod::create(123, 'menu'));

        $this->bot->expects($this->never())->method('editMessageText');
        $this->bot->expects($this->once())->method('sendMessage');

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
