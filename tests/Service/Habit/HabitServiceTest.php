<?php

declare(strict_types=1);

namespace App\Tests\Service\Habit;

use App\Entity\Habit;
use App\Entity\User;
use App\Repository\HabitRepository;
use App\Service\Habit\Dto\CreateHabitRequest;
use App\Service\Habit\Dto\UpdateHabitRequest;
use App\Service\Habit\HabitColor;
use App\Service\Habit\HabitService;
use App\Service\Habit\HabitState;
use App\Service\Habit\RemindService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class HabitServiceTest extends TestCase
{
    private HabitService $habitService;

    private HabitRepository&MockObject $habitRepository;

    private RemindService&MockObject $remindService;

    private TranslatorInterface&MockObject $translator;

    protected function setUp(): void
    {
        $this->habitRepository = $this->createMock(HabitRepository::class);
        $this->remindService = $this->createMock(RemindService::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->habitService = new HabitService(
            $this->habitRepository,
            $this->remindService,
            $this->translator,
        );
    }

    public function testCreateHabit(): void
    {
        $user = new User();
        $request = new CreateHabitRequest(
            name: 'Exercise',
            days: [0, 1, 2, 3, 4],
            time: '09:00',
            color: '#ef4444',
        );

        $nextRemind = new \DateTimeImmutable('2026-03-08 09:00:00');
        $this->remindService
            ->expects($this->once())
            ->method('getNextRemindTime')
            ->willReturn($nextRemind);

        $this->habitRepository
            ->expects($this->once())
            ->method('removeUserHabitsWithState')
            ->with($user, HabitState::Draft);

        $this->habitRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Habit::class));

        $habit = $this->habitService->createHabit($user, $request);

        $this->assertSame('Exercise', $habit->getDescription());
        $this->assertSame(HabitColor::Red, $habit->getColor());
        $this->assertTrue($habit->isPublished());
        $this->assertSame($user, $habit->getUser());
    }

    public function testCreateHabitRemovesDraftHabits(): void
    {
        $user = new User();
        $request = new CreateHabitRequest(
            name: 'Read',
            days: [0],
            time: '08:00',
        );

        $this->remindService->method('getNextRemindTime')
            ->willReturn(new \DateTimeImmutable());

        $this->habitRepository
            ->expects($this->once())
            ->method('removeUserHabitsWithState')
            ->with($user, HabitState::Draft);

        $this->habitService->createHabit($user, $request);
    }

    public function testPublishHabit(): void
    {
        $habit = $this->createHabit();
        $nextRemind = new \DateTimeImmutable('2026-03-08 09:00:00');

        $this->remindService
            ->expects($this->once())
            ->method('getNextRemindTime')
            ->willReturn($nextRemind);

        $this->habitRepository
            ->expects($this->once())
            ->method('save')
            ->with($habit);

        $this->habitService->publish($habit);

        $this->assertTrue($habit->isPublished());
    }

    public function testUpdateHabit(): void
    {
        $habit = $this->createHabit();
        $request = new UpdateHabitRequest(
            name: 'Updated Habit',
            days: [0, 1],
            time: '10:00',
            color: '#22c55e',
        );

        $nextRemind = new \DateTimeImmutable('2026-03-08 10:00:00');
        $this->remindService
            ->expects($this->once())
            ->method('getNextRemindTime')
            ->willReturn($nextRemind);

        $this->habitRepository
            ->expects($this->once())
            ->method('save')
            ->with($habit);

        $result = $this->habitService->updateHabit($habit, $request);

        $this->assertSame('Updated Habit', $result->getDescription());
        $this->assertSame(HabitColor::Green, $result->getColor());
    }

    public function testUpdateHabitRecalculatesRemindTime(): void
    {
        $habit = $this->createHabit();
        $request = new UpdateHabitRequest(
            name: 'Habit',
            days: [0, 2, 4],
            time: '14:00',
        );

        $this->remindService
            ->expects($this->once())
            ->method('getNextRemindTime')
            ->willReturn(new \DateTimeImmutable());

        $this->habitService->updateHabit($habit, $request);
    }

    public function testRemoveHabit(): void
    {
        $habit = $this->createHabit();

        $this->habitRepository
            ->expects($this->once())
            ->method('delete')
            ->with($habit);

        $this->habitService->removeHabit($habit);
    }

    public function testGetUserHabits(): void
    {
        $user = new User();
        $habits = [$this->createHabit(), $this->createHabit()];

        $this->habitRepository
            ->expects($this->once())
            ->method('findByUser')
            ->with($user)
            ->willReturn($habits);

        $result = $this->habitService->getUserHabits($user);

        $this->assertCount(2, $result);
    }

    public function testGetHabitPreviewText(): void
    {
        $habit = $this->createHabit();
        $habit->setDescription('Test *habit*');
        $habit->setRemindAt(new \DateTimeImmutable('09:00'));

        $this->remindService
            ->method('getRemindDaysAsString')
            ->willReturn('Mon, Tue');

        $this->translator
            ->method('trans')
            ->willReturn('Remind: Mon, Tue at 09:00');

        $result = $this->habitService->getHabitPreviewText($habit);

        $this->assertStringContainsString('Test \*habit\*', $result);
        $this->assertStringContainsString('Remind: Mon, Tue at 09:00', $result);
    }

    private function createHabit(): Habit
    {
        $user = new User();
        $user->setTimezone(new \DateTimeZone('UTC'));

        $habit = new Habit();
        $habit->setUser($user);
        $habit->setDescription('Test Habit');
        $habit->setRemindWeekDays(127);
        $habit->setRemindAt(new \DateTimeImmutable('09:00'));

        return $habit;
    }
}
