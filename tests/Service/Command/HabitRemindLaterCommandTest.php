<?php

declare(strict_types=1);

namespace App\Tests\Service\Command;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\HabitRemindLaterCommand;
use App\Service\Habit\HabitService;
use App\Service\Habit\RemindLaterService;
use App\Service\Message\Animation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\UpdateType;

class HabitRemindLaterCommandTest extends TestCase
{
    private HabitRemindLaterCommand $command;

    private BotApiComplete&MockObject $bot;

    private HabitService&MockObject $habitService;

    private TranslatorInterface&MockObject $translator;

    private Animation&MockObject $animation;

    private RemindLaterService&MockObject $remindLaterService;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->habitService = $this->createMock(HabitService::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->animation = $this->createMock(Animation::class);
        $this->remindLaterService = $this->createMock(RemindLaterService::class);

        $this->command = new HabitRemindLaterCommand(
            $this->bot,
            $this->habitService,
            $this->translator,
            $this->animation,
            $this->remindLaterService,
        );
    }

    public function testCanRunWithBusyCallback(): void
    {
        $update = new UpdateType();
        $user = new User();
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitBusy;

        $this->assertTrue($this->command->canRun($update, $user, $callback));
    }

    public function testRunWithRemindMinutesGreaterThanZero(): void
    {
        $user = new User();
        $habit = new Habit();
        $habit->setUser($user);

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitBusy;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitById')->willReturn($habit);
        $this->remindLaterService->expects($this->once())
            ->method('remindLater')
            ->willReturn(5);

        $this->translator->method('trans')->willReturn('Remind in %d min');

        $this->bot->expects($this->once())->method('editMessageReplyMarkup');
        $this->bot->expects($this->once())->method('editMessageText');
        $this->bot->expects($this->never())->method('sendAnimation');

        $this->command->run($update, $user, $callback);
    }

    public function testRunWithRemindMinutesNull(): void
    {
        $user = new User();
        $habit = new Habit();
        $habit->setUser($user);

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitBusy;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitById')->willReturn($habit);
        $this->remindLaterService->method('remindLater')->willReturn(0);

        $this->translator->method('trans')->willReturn('No more reminders');
        $this->animation->method('getByType')->willReturn('animation_id');

        $this->bot->expects($this->once())->method('editMessageReplyMarkup');
        $this->bot->expects($this->once())->method('editMessageText');
        $this->bot->expects($this->once())->method('sendAnimation');

        $this->command->run($update, $user, $callback);
    }

    public function testRunReturnsEarlyForWrongUser(): void
    {
        $user = new User();
        $otherUser = new User();
        $habit = new Habit();
        $habit->setUser($otherUser);

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitBusy;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = new UpdateType();

        $this->habitService->method('getHabitById')->willReturn($habit);
        $this->bot->expects($this->never())->method('editMessageReplyMarkup');

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
