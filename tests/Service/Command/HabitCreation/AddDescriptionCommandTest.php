<?php

declare(strict_types=1);

namespace App\Tests\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\HabitCreation\AddDescriptionCommand;
use App\Service\Habit\HabitService;
use App\Service\InputHandler;
use App\Service\Keyboard\HabitRemindDayInlineKeyboard;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddDescriptionCommandTest extends TestCase
{
    private AddDescriptionCommand $command;

    private BotApiComplete&MockObject $bot;

    private HabitService&MockObject $habitService;

    private ValidatorInterface&MockObject $validator;

    private InputHandler&MockObject $inputHandler;

    private HabitRemindDayInlineKeyboard&MockObject $habitRemindDayInlineKeyboard;

    private TranslatorInterface&MockObject $translator;

    protected function setUp(): void
    {
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->habitService = $this->createMock(HabitService::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->inputHandler = $this->createMock(InputHandler::class);
        $this->habitRemindDayInlineKeyboard = $this->createMock(HabitRemindDayInlineKeyboard::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->command = new AddDescriptionCommand(
            $this->bot,
            $this->habitService,
            $this->validator,
            $this->inputHandler,
            $this->habitRemindDayInlineKeyboard,
            $this->translator,
        );
    }

    public function testCanRunWithCorrectCallback(): void
    {
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetHabitDescription;

        $this->assertTrue($this->command->canRun(new UpdateType(), new User(), $callback));
    }

    public function testCanRunWithNull(): void
    {
        $this->assertFalse($this->command->canRun(new UpdateType(), new User(), null));
    }

    public function testRunWithNullMessage(): void
    {
        $update = new UpdateType();
        $update->message = null;
        $user = new User();
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetHabitDescription;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $this->bot->expects($this->never())->method('sendMessage');

        $this->command->run($update, $user, $callback);
    }

    public function testRunWithValidDescription(): void
    {
        $user = new User();
        $habit = $this->createHabitWithId();

        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetHabitDescription;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = new UpdateType();
        $update->message = new \TgBotApi\BotApiBase\Type\MessageType();
        $update->message->text = 'My habit';
        $update->message->chat = new \TgBotApi\BotApiBase\Type\ChatType();
        $update->message->chat->id = 123;

        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $this->habitService->method('getHabitByIdWithState')->willReturn($habit);
        $this->habitService->expects($this->once())->method('save')->with($habit);

        $this->inputHandler->expects($this->once())->method('unwaitForInput')->with($user);

        $this->translator->method('trans')->willReturn('Choose days');

        $this->bot->expects($this->once())->method('sendMessage');

        $this->command->run($update, $user, $callback);
    }

    public function testRunWithValidationError(): void
    {
        $user = new User();
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetHabitDescription;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = new UpdateType();
        $update->message = new \TgBotApi\BotApiBase\Type\MessageType();
        $update->message->text = '';
        $update->message->chat = new \TgBotApi\BotApiBase\Type\ChatType();
        $update->message->chat->id = 123;

        $violation = new ConstraintViolation('Error', null, [], null, '', null);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList([$violation]));

        $this->translator->method('trans')->willReturn('Error text');

        $this->habitService->expects($this->never())->method('save');
        $this->bot->expects($this->once())->method('sendMessage');

        $this->command->run($update, $user, $callback);
    }

    public function testRunWithSaveException(): void
    {
        $user = new User();
        $callback = new CommandCallback();
        $callback->command = CommandCallbackEnum::SetHabitDescription;
        $callback->parameters = [
            'id' => 'some-id',
        ];

        $update = new UpdateType();
        $update->message = new \TgBotApi\BotApiBase\Type\MessageType();
        $update->message->text = 'My habit';
        $update->message->chat = new \TgBotApi\BotApiBase\Type\ChatType();
        $update->message->chat->id = 123;

        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $this->habitService->method('getHabitByIdWithState')
            ->willThrowException(new \RuntimeException('Not found'));

        $this->translator->method('trans')->willReturn('Error');

        $this->inputHandler->expects($this->never())->method('unwaitForInput');
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
}
