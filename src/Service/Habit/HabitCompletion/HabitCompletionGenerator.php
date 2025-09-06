<?php

declare(strict_types=1);

namespace App\Service\Habit\HabitCompletion;

use App\Entity\Habit;
use App\Repository\MetricRepository;

class HabitCompletionGenerator
{
    private const DEFAULT_DAYS_BACK = 100;

    public function __construct(
        private readonly MetricRepository $metricRepository,
    ) {
    }

    /**
     * @return array<array{date: string, completed: boolean}>
     */
    public function generateCompletions(
        Habit $habit,
        int $daysBack = self::DEFAULT_DAYS_BACK
    ): array {
        $endDate = new \DateTimeImmutable();
        $startDate = $endDate->modify("-{$daysBack} days");

        $metrics = $this->metricRepository->findHabitDoneInDateRange($habit, $startDate, $endDate);

        $completionsByDate = [];

        foreach ($metrics as $metric) {
            $dateKey = $metric->getMetricDate()->format('Y-m-d');
            $completionsByDate[$dateKey] = true;
        }

        $completions = [];
        $currentDate = $startDate;

        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $completions[] = [
                'date' => $dateKey,
                'completed' => isset($completionsByDate[$dateKey]),
            ];

            $currentDate = $currentDate->modify('+1 day');
        }

        return $completions;
    }
}
