<?php

declare(strict_types=1);

namespace App\Tests\Service\Habit\HabitCompletion;

use App\Entity\Habit;
use App\Entity\User;
use App\Repository\MetricRepository;
use App\Service\Habit\HabitCompletion\HabitCompletionGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HabitCompletionGeneratorTest extends TestCase
{
    private HabitCompletionGenerator $generator;

    private MetricRepository&MockObject $metricRepository;

    protected function setUp(): void
    {
        $this->metricRepository = $this->createMock(MetricRepository::class);
        $this->generator = new HabitCompletionGenerator($this->metricRepository);
    }

    public function testGenerateCompletionsWithCompletedDays(): void
    {
        $habit = $this->createHabit();

        // Create metrics for specific dates relative to today
        $today = new \DateTimeImmutable();
        $yesterday = $today->modify('-1 day');
        $dayBeforeYesterday = $today->modify('-2 days');

        $metric1 = $this->createMock(\App\Entity\Metric::class);
        $metric1->method('getMetricDate')->willReturn($yesterday->setTime(10, 0, 0));

        $metric2 = $this->createMock(\App\Entity\Metric::class);
        $metric2->method('getMetricDate')->willReturn($dayBeforeYesterday->setTime(9, 0, 0));

        $this->metricRepository
            ->expects($this->once())
            ->method('findHabitDoneInDateRange')
            ->willReturn([$metric1, $metric2]);

        $completions = $this->generator->generateCompletions($habit, 3);

        // When we ask for 3 days back, we get 4 days total (including today)
        $this->assertCount(4, $completions);

        // Check that the last two days have completions
        $this->assertTrue($completions[1]['completed']); // yesterday
        $this->assertTrue($completions[2]['completed']); // day before yesterday
        $this->assertFalse($completions[0]['completed']); // today (no metric)
        $this->assertFalse($completions[3]['completed']); // 3 days ago (no metric)
    }

    public function testGenerateCompletionsWithoutCompletedDays(): void
    {
        $habit = $this->createHabit();

        $this->metricRepository
            ->expects($this->once())
            ->method('findHabitDoneInDateRange')
            ->willReturn([]);

        $completions = $this->generator->generateCompletions($habit, 2);

        // When we ask for 2 days back, we get 3 days total (including today)
        $this->assertCount(3, $completions);
        $this->assertFalse($completions[0]['completed']);
        $this->assertFalse($completions[1]['completed']);
        $this->assertFalse($completions[2]['completed']);
    }

    private function createHabit(): Habit
    {
        $user = new User();
        $user->setTimezone(new \DateTimeZone('UTC'));

        $habit = new Habit();
        $habit->setUser($user);
        $habit->setDescription('Test Habit');
        $habit->setRemindWeekDays(127); // All days
        $habit->setRemindAt(new \DateTimeImmutable('09:00'));

        return $habit;
    }
}
