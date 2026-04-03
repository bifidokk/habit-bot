<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\HabitCreation\BackToDescriptionCommand;
use App\Service\Habit\HabitService;
use App\Service\InputHandler;
use App\Service\Keyboard\MainMenuKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\UpdateType;

class BackToDescriptionCommandTest extends TestCase
{
    private BackToDescriptionCommand $command;

    private BotApiComplete&MockObject $bot;

    private HabitService&MockObject $habitService;

    private InputHandler&MockObject $inputHandler;

    private TranslatorInterface&MockObject $translator;

    private MainMenuKeyboard&MockObject $mainMenuKeyboard;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->habitService = $this->createMock(HabitService::class);
        $this->inputHandler = $this->createMock(InputHandler::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->mainMenuKeyboard = $this->createMock(MainMenuKeyboard::class);

        $this->command = new BackToDescriptionCommand(
            $this->bot,
            $this->habitService,
            $this->inputHandler,
            $this->translator,
            $this->mainMenuKeyboard,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::BackToDescription;

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
        $callback->command = CommandCallbackEnum::BackToDescription;
        $callback->parameters = [
            'id' => $habit->getId()->toRfc4122(),
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);
        $this->translator->method('trans')->willReturn('Enter description');

        $this->bot->expects($this->once())->method('deleteMessage');
        $this->inputHandler->expects($this->once())->method('waitForInput');
        $this->bot->expects($this->once())->method('sendMessage');

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
