<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Entity\Habit;
use App\Entity\Metric;
use App\Entity\User;
use App\Event\Habit\HabitDoneEvent;
use App\EventListener\HabitDoneSubscriber;
use App\Repository\MetricRepository;
use App\Service\Metric\MetricService;
use App\Service\Metric\MetricType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HabitDoneSubscriberTest extends TestCase
{
    private HabitDoneSubscriber $subscriber;

    private MetricService&MockObject $metricService;

    private MetricRepository&MockObject $metricRepository;

    protected function setUp(): void
    {
        $this->metricService = $this->createMock(MetricService::class);
        $this->metricRepository = $this->createMock(MetricRepository::class);

        $this->subscriber = new HabitDoneSubscriber(
            $this->metricService,
            $this->metricRepository,
        );
    }

    public function testMetricCreatedOnFirstCompletion(): void
    {
        $habit = $this->createHabit();
        $date = new \DateTimeImmutable('2026-03-07');
        $event = new HabitDoneEvent($habit, $date);

        $this->metricRepository
            ->expects($this->once())
            ->method('findByHabitOnDate')
            ->with($habit, $date)
            ->willReturn([]);

        $this->metricService
            ->expects($this->once())
            ->method('addHabitMetric')
            ->with(MetricType::HabitDone, $date, $habit);

        $this->subscriber->updateHabitDoneMetric($event);
    }

    public function testMetricNotDuplicatedOnSecondCompletion(): void
    {
        $habit = $this->createHabit();
        $date = new \DateTimeImmutable('2026-03-07');
        $event = new HabitDoneEvent($habit, $date);

        $existingMetric = new Metric();
        $this->metricRepository
            ->expects($this->once())
            ->method('findByHabitOnDate')
            ->with($habit, $date)
            ->willReturn([$existingMetric]);

        $this->metricService
            ->expects($this->never())
            ->method('addHabitMetric');

        $this->subscriber->updateHabitDoneMetric($event);
    }

    public function testSubscribedEvents(): void
    {
        $events = HabitDoneSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(HabitDoneEvent::class, $events);
        $this->assertSame('updateHabitDoneMetric', $events[HabitDoneEvent::class]);
    }

    private function createHabit(): Habit
    {
        $user = new User();
        $habit = new Habit();
        $habit->setUser($user);
        $habit->setDescription('Test Habit');

        return $habit;
    }
}
