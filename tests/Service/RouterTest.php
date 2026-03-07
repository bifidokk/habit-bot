<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\InputHandler;
use App\Service\Router;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TgBotApi\BotApiBase\Type\CallbackQueryType;
use TgBotApi\BotApiBase\Type\UpdateType;

class RouterTest extends TestCase
{
    private Router $router;

    private ServiceLocator&MockObject $commandLocator;

    private InputHandler&MockObject $inputHandler;

    protected function setUp(): void
    {
        $this->commandLocator = $this->createMock(ServiceLocator::class);
        $this->inputHandler = $this->createMock(InputHandler::class);

        $this->router = new Router(
            $this->commandLocator,
            $this->inputHandler,
        );
    }

    public function testRunCallsMatchingCommand(): void
    {
        $update = new UpdateType();
        $user = new User();

        $command = $this->createMock(CommandInterface::class);
        $command->method('canRun')->willReturn(true);
        $command->method('getPriority')->willReturn(CommandPriority::Low);
        $command->expects($this->once())->method('run');

        $this->inputHandler->method('checkForInput')->willReturn(null);

        $this->commandLocator->method('getProvidedServices')
            ->willReturn([
                'test_command' => CommandInterface::class,
            ]);
        $this->commandLocator->method('get')
            ->with('test_command')
            ->willReturn($command);

        $this->router->run($update, $user);
    }

    public function testRunNoMatchingCommand(): void
    {
        $update = new UpdateType();
        $user = new User();

        $command = $this->createMock(CommandInterface::class);
        $command->method('canRun')->willReturn(false);
        $command->method('getPriority')->willReturn(CommandPriority::Low);
        $command->expects($this->never())->method('run');

        $this->inputHandler->method('checkForInput')->willReturn(null);

        $this->commandLocator->method('getProvidedServices')
            ->willReturn([
                'test_command' => CommandInterface::class,
            ]);
        $this->commandLocator->method('get')
            ->with('test_command')
            ->willReturn($command);

        $this->router->run($update, $user);
    }

    public function testGetCommandByName(): void
    {
        $command = $this->createMock(CommandInterface::class);

        $this->commandLocator->expects($this->once())
            ->method('get')
            ->with('my_command')
            ->willReturn($command);

        $result = $this->router->getCommandByName('my_command');

        $this->assertSame($command, $result);
    }

    public function testCallbackFromInputHandlerTakesPrecedence(): void
    {
        $update = new UpdateType();
        $update->callbackQuery = new CallbackQueryType();
        $update->callbackQuery->data = '/listHabit?page=0';

        $user = new User();

        $this->inputHandler->method('checkForInput')
            ->willReturn('/done?id=abc');

        $command = $this->createMock(CommandInterface::class);
        $command->method('getPriority')->willReturn(CommandPriority::Low);
        $command->method('canRun')->willReturnCallback(
            function (UpdateType $u, User $usr, ?CommandCallback $cb) {
                return $cb !== null && $cb->command === CommandCallbackEnum::HabitDone;
            }
        );
        $command->expects($this->once())->method('run');

        $this->commandLocator->method('getProvidedServices')
            ->willReturn([
                'test_command' => CommandInterface::class,
            ]);
        $this->commandLocator->method('get')
            ->with('test_command')
            ->willReturn($command);

        $this->router->run($update, $user);
    }

    public function testCallbackFromCallbackQuery(): void
    {
        $update = new UpdateType();
        $update->callbackQuery = new CallbackQueryType();
        $update->callbackQuery->data = '/listHabit?page=2';

        $user = new User();

        $this->inputHandler->method('checkForInput')->willReturn(null);

        $command = $this->createMock(CommandInterface::class);
        $command->method('getPriority')->willReturn(CommandPriority::Low);
        $command->method('canRun')->willReturnCallback(
            function (UpdateType $u, User $usr, ?CommandCallback $cb) {
                return $cb !== null && $cb->command === CommandCallbackEnum::HabitList;
            }
        );
        $command->expects($this->once())->method('run');

        $this->commandLocator->method('getProvidedServices')
            ->willReturn([
                'test_command' => CommandInterface::class,
            ]);
        $this->commandLocator->method('get')
            ->with('test_command')
            ->willReturn($command);

        $this->router->run($update, $user);
    }
}
