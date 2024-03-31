<?php

declare(strict_types=1);

namespace App\Service\Metric;

use App\Entity\Habit;
use App\Entity\Metric;
use App\Repository\MetricRepository;

class MetricService
{
    public function __construct(
        private readonly MetricRepository $metricRepository,
    ) {
    }

    public function addHabitMetric(
        MetricType $metricType,
        \DateTimeImmutable $metricDate,
        Habit $habit,
    ): void {
        $metric = new Metric();
        $metric->setHabit($habit);
        $metric->setType($metricType);
        $metric->setMetricDate($metricDate);

        $this->metricRepository->save($metric);
    }
}
