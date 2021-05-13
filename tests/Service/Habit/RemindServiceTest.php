<?php

declare(strict_types=1);

namespace App\Tests\Service\Habit;

use App\Entity\Habit;
use App\Repository\HabitRepository;
use App\Service\Habit\RemindService;
use PHPUnit\Framework\TestCase;

class RemindServiceTest extends TestCase
{
    private RemindService $remindService;

    protected function setUp(): void
    {
        parent::setUp();

        $habitRepository = $this->createMock(HabitRepository::class);
        $this->remindService = new RemindService($habitRepository);
    }

    /**
     * @test
     */
    public function itAddsRemindDaysForHabitTest(): void
    {
        $habit = new Habit();
        $this->remindService->toggleDay($habit, 1);

        $this->assertEquals(32, $habit->getRemindWeekDays());

        $habit = new Habit();
        $this->remindService->toggleDay($habit, 5);

        $this->assertEquals(2, $habit->getRemindWeekDays());
    }

    /**
     * @test
     */
    public function itDoesntAddInvalidNumberOfDayTest(): void
    {
        $habit = new Habit();
        $this->remindService->toggleDay($habit, 7);

        $this->assertEquals(0, $habit->getRemindWeekDays());

        $habit = new Habit();
        $this->remindService->toggleDay($habit, -5);

        $this->assertEquals(0, $habit->getRemindWeekDays());
    }

    /**
     * @test
     */
    public function itRemovesRemindDaysForHabitTest(): void
    {
        $habit = new Habit();

        $this->remindService->toggleDay($habit, 5);
        $this->assertEquals(2, $habit->getRemindWeekDays());

        $this->remindService->toggleDay($habit, 5);
        $this->assertEquals(0, $habit->getRemindWeekDays());
    }

    /**
     * @test
     */
    public function itMarkAllRemindDaysForHabitTest(): void
    {
        $habit = new Habit();
        $this->remindService->markAll($habit);
        $this->assertEquals(127, $habit->getRemindWeekDays());
    }

    /**
     * @test
     */
    public function itConvertsRemindDaysToListWithDayNamesTest(): void
    {
        $habit = new Habit();

        $this->remindService->toggleDay($habit, 1);
        $days = $this->remindService->getRemindDaysAsString($habit);
        $this->assertEquals('Mon', $days);

        $habit = new Habit();
        $this->remindService->toggleDay($habit, 1);
        $this->remindService->toggleDay($habit, 3);
        $days = $this->remindService->getRemindDaysAsString($habit);
        $this->assertEquals('Mon, Wed', $days);

        $habit = new Habit();
        $this->remindService->markAll($habit);
        $days = $this->remindService->getRemindDaysAsString($habit);
        $this->assertEquals('Sun, Mon, Tue, Wed, Thu, Fri, Sat', $days);
    }

    /**
     * @test
     */
    public function itCreatesNextRemindTime(): void
    {
        $habit = new Habit();
        $this->remindService->toggleDay($habit, 1); //mon
        $habit->setRemindAt(new \DateTimeImmutable('15:00'));

        $currentTime = new \DateTimeImmutable('Sun this week');
        $nextRemindTime = $this->remindService->getNextRemindTime($currentTime, $habit);
        $this->assertEquals('Mon 15:00', $nextRemindTime->format('D H:i'));

        $habit = new Habit();
        $this->remindService->toggleDay($habit, 1); //mon
        $this->remindService->toggleDay($habit, 3); //wed
        $this->remindService->toggleDay($habit, 5); //fri
        $habit->setRemindAt(new \DateTimeImmutable('09:00'));

        $currentTime = new \DateTimeImmutable('Thu this week');
        $nextRemindTime = $this->remindService->getNextRemindTime($currentTime, $habit);
        $this->assertEquals('Fri 09:00', $nextRemindTime->format('D H:i'));

        $currentTime = new \DateTimeImmutable('Tue this week');
        $nextRemindTime = $this->remindService->getNextRemindTime($currentTime, $habit);
        $this->assertEquals('Wed 09:00', $nextRemindTime->format('D H:i'));

        $currentTime = new \DateTimeImmutable('Fri 08:00 this week');
        $nextRemindTime = $this->remindService->getNextRemindTime($currentTime, $habit);
        $this->assertEquals('Fri 09:00', $nextRemindTime->format('D H:i'));

        $currentTime = new \DateTimeImmutable('Fri 09:00 this week');
        $nextRemindTime = $this->remindService->getNextRemindTime($currentTime, $habit);
        $this->assertEquals('Fri 09:00', $nextRemindTime->format('D H:i'));

        $currentTime = new \DateTimeImmutable('Sun 09:00 this week');
        $nextRemindTime = $this->remindService->getNextRemindTime($currentTime, $habit);
        $this->assertEquals('Mon 09:00', $nextRemindTime->format('D H:i'));
    }
}
