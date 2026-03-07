<?php

declare(strict_types=1);

namespace App\Tests\Service\Habit\HabitCompletion;

use App\Entity\Habit;
use App\Entity\Metric;
use App\Entity\User;
use App\Event\Habit\HabitDoneEvent;
use App\Repository\MetricRepository;
use App\Service\Habit\Exception\CouldNotCreateHabitCompletion;
use App\Service\Habit\HabitCompletion\HabitCompleteRequest;
use App\Service\Habit\HabitCompletion\HabitCompletionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class HabitCompletionServiceTest extends TestCase
{
    private HabitCompletionService $service;

    private EventDispatcherInterface&MockObject $eventDispatcher;

    private MetricRepository&MockObject $metricRepository;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->metricRepository = $this->createMock(MetricRepository::class);

        $this->service = new HabitCompletionService(
            $this->eventDispatcher,
            $this->metricRepository,
        );
    }

    public function testCompleteHabitDispatchesEvent(): void
    {
        $habit = $this->createPublishedHabit();
        $request = new HabitCompleteRequest(date: '2026-03-07', completed: true);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (HabitDoneEvent $event) use ($habit) {
                return $event->getHabit() === $habit
                    && $event->getDate()->format('Y-m-d') === '2026-03-07';
            }));

        $this->service->createHabitCompletion($habit, $request);
    }

    public function testUncompleteHabitDeletesMetrics(): void
    {
        $habit = $this->createPublishedHabit();
        $request = new HabitCompleteRequest(date: '2026-03-07', completed: false);

        $metric1 = new Metric();
        $metric2 = new Metric();

        $this->metricRepository
            ->expects($this->once())
            ->method('findHabitDoneOnDate')
            ->with($habit, $this->isInstanceOf(\DateTimeImmutable::class))
            ->willReturn([$metric1, $metric2]);

        $this->metricRepository
            ->expects($this->exactly(2))
            ->method('remove');

        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->service->createHabitCompletion($habit, $request);
    }

    public function testUncompleteHabitNoMetricsToDelete(): void
    {
        $habit = $this->createPublishedHabit();
        $request = new HabitCompleteRequest(date: '2026-03-07', completed: false);

        $this->metricRepository
            ->expects($this->once())
            ->method('findHabitDoneOnDate')
            ->willReturn([]);

        $this->metricRepository
            ->expects($this->never())
            ->method('remove');

        $this->service->createHabitCompletion($habit, $request);
    }

    public function testUnpublishedHabitThrows(): void
    {
        $habit = new Habit();
        $request = new HabitCompleteRequest(date: '2026-03-07', completed: true);

        $this->expectException(CouldNotCreateHabitCompletion::class);

        $this->service->createHabitCompletion($habit, $request);
    }

    private function createPublishedHabit(): Habit
    {
        $user = new User();
        $habit = new Habit();
        $habit->setUser($user);
        $habit->setDescription('Test Habit');
        $habit->setRemindWeekDays(127);
        $habit->setRemindAt(new \DateTimeImmutable('09:00'));
        $habit->publish();

        return $habit;
    }
}
