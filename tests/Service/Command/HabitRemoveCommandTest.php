<?php

declare(strict_types=1);

namespace App\Tests\Service\Command;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Command\HabitListCommand;
use App\Service\Command\HabitRemoveCommand;
use App\Service\Habit\HabitService;
use App\Service\Message\Animation;
use App\Service\Router;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\UpdateType;

class HabitRemoveCommandTest extends TestCase
{
    private HabitRemoveCommand $command;

    private BotApiComplete&MockObject $bot;

    private HabitService&MockObject $habitService;

    private TranslatorInterface&MockObject $translator;

    private Animation&MockObject $animation;

    private Router&MockObject $router;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->habitService = $this->createMock(HabitService::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->animation = $this->createMock(Animation::class);
        $this->router = $this->createMock(Router::class);

        $this->command = new HabitRemoveCommand(
            $this->bot,
            $this->habitService,
            $this->translator,
            $this->animation,
            $this->router,
        );
    }

    public function testCanRunWithRemoveCallback(): void
    {
        $update = new UpdateType();
        $user = new User();
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitRemove;

        $this->assertTrue($this->command->canRun($update, $user, $callback));
    }

    public function testRunNotConfirmedDelegatesToHabitList(): void
    {
        $user = new User();
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitRemove;
        $callback->parameters = [
            'c' => '0',
            'id' => 'some-id',
        ];

        $update = new UpdateType();

        $listCommand = $this->createMock(CommandInterface::class);
        $listCommand->expects($this->once())
            ->method('run')
            ->with($update, $user, $callback);

        $this->router->expects($this->once())
            ->method('getCommandByName')
            ->with(HabitListCommand::COMMAND_NAME)
            ->willReturn($listCommand);

        $this->habitService->expects($this->never())->method('removeHabit');

        $this->command->run($update, $user, $callback);
    }

    public function testRunConfirmedCorrectUser(): void
    {
        $user = new User();
        $habit = new Habit();
        $habit->setUser($user);

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitRemove;
        $callback->parameters = [
            'c' => '1',
            'id' => 'some-id',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitById')->willReturn($habit);
        $this->habitService->expects($this->once())->method('removeHabit')->with($habit);

        $this->translator->method('trans')->willReturn('Removed');
        $this->animation->method('getByType')->willReturn('animation_id');

        $this->bot->expects($this->once())->method('editMessageText');
        $this->bot->expects($this->once())->method('sendAnimation');

        $this->command->run($update, $user, $callback);
    }

    public function testRunConfirmedWrongUser(): void
    {
        $user = new User();
        $otherUser = new User();
        $habit = new Habit();
        $habit->setUser($otherUser);

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitRemove;
        $callback->parameters = [
            'c' => '1',
            'id' => 'some-id',
        ];

        $update = new UpdateType();

        $this->habitService->method('getHabitById')->willReturn($habit);
        $this->habitService->expects($this->never())->method('removeHabit');

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
