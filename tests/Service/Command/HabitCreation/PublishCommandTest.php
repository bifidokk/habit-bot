<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\HabitCreation\PublishCommand;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\MainMenuKeyboard;
use App\Service\Message\Animation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\UpdateType;

class PublishCommandTest extends TestCase
{
    private PublishCommand $command;

    private BotApiComplete&MockObject $bot;

    private HabitService&MockObject $habitService;

    private Animation&MockObject $animation;

    private TranslatorInterface&MockObject $translator;

    private MainMenuKeyboard&MockObject $mainMenuKeyboard;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->habitService = $this->createMock(HabitService::class);
        $this->animation = $this->createMock(Animation::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->mainMenuKeyboard = $this->createMock(MainMenuKeyboard::class);

        $this->command = new PublishCommand(
            $this->bot,
            $this->habitService,
            $this->animation,
            $this->translator,
            $this->mainMenuKeyboard,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitPublish;

        $this->assertTrue($this->command->canRun(new UpdateType(), new User(), $callback));
    }

    public function testCanRunWithNull(): void
    {
        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), null));
    }

    public function testRunPublishesReadyHabit(): void
    {
        $user = new User();
        $user->toggleShowAnimations();
        $habit = new Habit();
        $habit->setDescription('Test');
        $habit->setRemindWeekDays(127);
        $habit->setRemindAt(new \DateTimeImmutable('09:00'));

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitPublish;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);
        $this->habitService->expects($this->once())->method('publish')->with($habit);

        $this->translator->method('trans')->willReturn('Published');
        $this->animation->method('getByType')->willReturn('animation_id');

        $this->bot->expects($this->once())->method('editMessageText');
        $this->bot->expects($this->once())->method('sendAnimation');

        $this->command->run($update, $user, $callback);
    }

    public function testRunDoesNotSendAnimationWhenDisabled(): void
    {
        $user = new User();
        $habit = new Habit();
        $habit->setDescription('Test');
        $habit->setRemindWeekDays(127);
        $habit->setRemindAt(new \DateTimeImmutable('09:00'));

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitPublish;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);
        $this->habitService->expects($this->once())->method('publish')->with($habit);

        $this->translator->method('trans')->willReturn('Published');

        $this->bot->expects($this->once())->method('editMessageText');
        $this->bot->expects($this->never())->method('sendAnimation');

        $this->command->run($update, $user, $callback);
    }

    public function testRunReturnsEarlyIfNotReady(): void
    {
        $user = new User();
        $habit = new Habit();

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitPublish;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = new UpdateType();

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);
        $this->habitService->expects($this->never())->method('publish');
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
