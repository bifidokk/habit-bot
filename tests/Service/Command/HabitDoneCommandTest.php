<?php

declare(strict_types=1);

namespace App\Tests\Service\Command;

use App\Entity\Habit;
use App\Entity\User;
use App\Event\Habit\HabitDoneEvent;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\HabitDoneCommand;
use App\Service\Habit\HabitService;
use App\Service\Message\Animation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\UpdateType;

class HabitDoneCommandTest extends TestCase
{
    private HabitDoneCommand $command;

    private BotApiComplete&MockObject $bot;

    private HabitService&MockObject $habitService;

    private TranslatorInterface&MockObject $translator;

    private Animation&MockObject $animation;

    private EventDispatcherInterface&MockObject $eventDispatcher;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->habitService = $this->createMock(HabitService::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->animation = $this->createMock(Animation::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->command = new HabitDoneCommand(
            $this->bot,
            $this->habitService,
            $this->translator,
            $this->animation,
            $this->eventDispatcher,
        );
    }

    public function testGetNameAndPriority(): void
    {
        $this->assertSame('command', $this->command->getName());
        $this->assertSame(\App\Service\Command\CommandPriority::Low, $this->command->getPriority());
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $update = new UpdateType();
        $user = new User();
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitDone;

        $this->assertTrue($this->command->canRun($update, $user, $callback));
    }

    public function testCanRunWithWrongCallback(): void
    {
        $update = new UpdateType();
        $user = new User();
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitList;

        $this->assertFalse($this->command->canRun($update, $user, $callback));
    }

    public function testCanRunWithNullCallback(): void
    {
        $update = new UpdateType();
        $user = new User();

        $this->assertFalse($this->command->canRun($update, $user, null));
    }

    public function testRunDispatchesEventAndSendsMessages(): void
    {
        $user = new User();
        $habit = new Habit();
        $habit->setUser($user);

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitDone;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->expects($this->once())
            ->method('getHabitById')
            ->with('some-id')
            ->willReturn($habit);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(HabitDoneEvent::class));

        $this->translator->method('trans')->willReturn('Done!');
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
        $callback->command = CommandCallbackEnum::HabitDone;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = new UpdateType();

        $this->habitService->expects($this->once())
            ->method('getHabitById')
            ->willReturn($habit);

        $this->eventDispatcher->expects($this->never())->method('dispatch');
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
