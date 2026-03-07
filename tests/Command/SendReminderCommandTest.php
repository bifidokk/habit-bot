<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\SendReminderCommand;
use App\Entity\Habit;
use App\Entity\User;
use App\Repository\HabitRepository;
use App\Service\Habit\RemindService;
use App\Service\Keyboard\HabitDoneKeyboard;
use App\Service\User\UserService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class SendReminderCommandTest extends TestCase
{
    private HabitRepository&MockObject $habitRepository;

    private BotApiComplete&MockObject $bot;

    private RemindService&MockObject $remindService;

    private HabitDoneKeyboard&MockObject $habitDoneKeyboard;

    private LoggerInterface&MockObject $logger;

    private UserService&MockObject $userService;

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->habitRepository = $this->createMock(HabitRepository::class);
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->remindService = $this->createMock(RemindService::class);
        $this->habitDoneKeyboard = $this->createMock(HabitDoneKeyboard::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userService = $this->createMock(UserService::class);

        $command = new SendReminderCommand(
            $this->habitRepository,
            $this->bot,
            $this->remindService,
            $this->habitDoneKeyboard,
            $this->logger,
            $this->userService,
        );

        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithNoHabits(): void
    {
        $this->habitRepository->method('findReadyForRemindHabits')->willReturn([]);
        $this->bot->expects($this->never())->method('sendMessage');

        $this->commandTester->execute([]);

        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteSendsReminder(): void
    {
        $user = new User();
        $reflection = new \ReflectionProperty(User::class, 'telegramId');
        $reflection->setValue($user, 12345);

        $habit = new Habit();
        $habit->setUser($user);
        $habit->setDescription('Exercise');
        $habit->setRemindWeekDays(127);
        $habit->setRemindAt(new \DateTimeImmutable('09:00'));

        $this->habitRepository->method('findReadyForRemindHabits')->willReturn([$habit]);
        $this->habitDoneKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->expects($this->once())->method('sendMessage');

        $nextRemind = new \DateTimeImmutable('tomorrow 09:00');
        $this->remindService->method('getNextRemindTime')->willReturn($nextRemind);
        $this->habitRepository->expects($this->once())->method('save')->with($habit);

        $this->commandTester->execute([]);

        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteHandlesException(): void
    {
        $user = new User();
        $reflection = new \ReflectionProperty(User::class, 'telegramId');
        $reflection->setValue($user, 12345);

        $habit = new Habit();
        $habit->setUser($user);
        $habit->setDescription('Exercise');

        $this->habitRepository->method('findReadyForRemindHabits')->willReturn([$habit]);
        $this->habitDoneKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->method('sendMessage')
            ->willThrowException(new \RuntimeException('Network error'));

        $this->logger->expects($this->once())->method('error');
        $this->habitRepository->expects($this->never())->method('save');

        $this->commandTester->execute([]);

        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteDeactivatesUserOnForbidden(): void
    {
        $user = new User();
        $reflection = new \ReflectionProperty(User::class, 'telegramId');
        $reflection->setValue($user, 12345);

        $habit = new Habit();
        $habit->setUser($user);
        $habit->setDescription('Exercise');

        $this->habitRepository->method('findReadyForRemindHabits')->willReturn([$habit]);
        $this->habitDoneKeyboard->method('generate')
            ->willReturn(InlineKeyboardMarkupType::create([]));

        $this->bot->method('sendMessage')
            ->willThrowException(new \RuntimeException('Forbidden: bot was blocked by the user'));

        $this->logger->expects($this->once())->method('error');
        $this->userService->expects($this->once())->method('deactivateUser')->with($user);

        $this->commandTester->execute([]);

        $this->assertSame(0, $this->commandTester->getStatusCode());
    }
}
