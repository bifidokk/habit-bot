<?php

declare(strict_types=1);

namespace App\Tests\Service\Habit;

use App\Entity\Habit;
use App\Repository\HabitRepository;
use App\Service\Habit\RemindDayService;
use PHPUnit\Framework\TestCase;

class RemindDayServiceTest extends TestCase
{
    private RemindDayService $remindDayService;

    protected function setUp(): void
    {
        parent::setUp();

        $habitRepository = $this->createMock(HabitRepository::class);
        $this->remindDayService = new RemindDayService($habitRepository);
    }

    /**
     * @test
     */
    public function itAddsRemindDaysForHabitTest(): void
    {
        $habit = new Habit();
        $this->remindDayService->toggleDay($habit, 1);

        $this->assertEquals(32, $habit->getRemindWeekDays());

        $habit = new Habit();
        $this->remindDayService->toggleDay($habit, 5);

        $this->assertEquals(2, $habit->getRemindWeekDays());
    }

    /**
     * @test
     */
    public function itDoesntAddInvalidNumberOfDayTest(): void
    {
        $habit = new Habit();
        $this->remindDayService->toggleDay($habit, 7);

        $this->assertEquals(0, $habit->getRemindWeekDays());

        $habit = new Habit();
        $this->remindDayService->toggleDay($habit, -5);

        $this->assertEquals(0, $habit->getRemindWeekDays());
    }

    /**
     * @test
     */
    public function itRemovesRemindDaysForHabitTest(): void
    {
        $habit = new Habit();

        $this->remindDayService->toggleDay($habit, 5);
        $this->assertEquals(2, $habit->getRemindWeekDays());

        $this->remindDayService->toggleDay($habit, 5);
        $this->assertEquals(0, $habit->getRemindWeekDays());
    }

    /**
     * @test
     */
    public function itMarkAllRemindDaysForHabitTest(): void
    {
        $habit = new Habit();
        $this->remindDayService->markAll($habit);
        $this->assertEquals(127, $habit->getRemindWeekDays());
    }
}
