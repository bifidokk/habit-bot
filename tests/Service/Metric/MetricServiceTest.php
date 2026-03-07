<?php

declare(strict_types=1);

namespace App\Tests\Service\Metric;

use App\Entity\Habit;
use App\Entity\Metric;
use App\Entity\User;
use App\Repository\MetricRepository;
use App\Service\Metric\MetricService;
use App\Service\Metric\MetricType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MetricServiceTest extends TestCase
{
    private MetricService $metricService;

    private MetricRepository&MockObject $metricRepository;

    protected function setUp(): void
    {
        $this->metricRepository = $this->createMock(MetricRepository::class);
        $this->metricService = new MetricService($this->metricRepository);
    }

    public function testAddHabitMetric(): void
    {
        $habit = new Habit();
        $habit->setUser(new User());
        $habit->setDescription('Test');

        $metricDate = new \DateTimeImmutable('2026-03-07');

        $this->metricRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Metric $metric) use ($habit, $metricDate) {
                return $metric->getHabit() === $habit
                    && $metric->getType() === MetricType::HabitDone
                    && $metric->getMetricDate()->format('Y-m-d') === $metricDate->format('Y-m-d');
            }));

        $this->metricService->addHabitMetric(MetricType::HabitDone, $metricDate, $habit);
    }
}
