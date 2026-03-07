<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\HabitCreation\DescriptionFormCommand;
use App\Service\Habit\HabitService;
use App\Service\InputHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\UpdateType;

class DescriptionFormCommandTest extends TestCase
{
    private DescriptionFormCommand $command;

    private BotApiComplete&MockObject $bot;

    private InputHandler&MockObject $inputHandler;

    private HabitService&MockObject $habitService;

    private TranslatorInterface&MockObject $translator;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->inputHandler = $this->createMock(InputHandler::class);
        $this->habitService = $this->createMock(HabitService::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->command = new DescriptionFormCommand(
            $this->bot,
            $this->inputHandler,
            $this->habitService,
            $this->translator,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitDescriptionForm;

        $this->assertTrue($this->command->canRun(new UpdateType(), new User(), $callback));
    }

    public function testCanRunWithWrongCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitList;

        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), $callback));
    }

    public function testCanRunWithNull(): void
    {
        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), null));
    }

    public function testRunWaitsForInputAndEditsMessage(): void
    {
        $user = new User();
        $habit = $this->createHabitWithId();

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::HabitDescriptionForm;
        $callback->parameters = [
            'id' => $habit->getId()->toRfc4122(),
        ];

        $update = $this->createCallbackUpdate(123, 456);

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);

        $this->inputHandler->expects($this->once())
            ->method('waitForInput')
            ->with($user, $this->stringContains('/setDescription'));

        $this->translator->method('trans')->willReturn('Enter description');
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
        $reflection = new \ReflectionClass($habit);
        $property = $reflection->getProperty('id');
        $property->setValue($habit, Uuid::v4());

        return $habit;
    }
}
