<?php

declare(strict_types=1);

namespace App\Service\Habit\HabitCompletion;

use App\Entity\Habit;
use App\Event\Habit\HabitDoneEvent;
use App\Repository\MetricRepository;
use App\Service\Habit\Exception\CouldNotCreateHabitCompletion;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class HabitCompletionService
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MetricRepository $metricRepository,
    ) {
    }

    public function createHabitCompletion(
        Habit $habit,
        HabitCompleteRequest $habitCompleteRequest
    ): void {
        if (! $habit->isPublished()) {
            throw new CouldNotCreateHabitCompletion();
        }

        if (! $habitCompleteRequest->completed) {
            $this->deleteHabitMetrics($habit, $habitCompleteRequest->getDateAsDateTimeImmutable());

            return;
        }

        $this->eventDispatcher->dispatch(new HabitDoneEvent($habit, $habitCompleteRequest->getDateAsDateTimeImmutable()));
    }

    private function deleteHabitMetrics(Habit $habit, \DateTimeImmutable $date): void
    {
        $metrics = $this->metricRepository->findHabitDoneOnDate($habit, $date);

        foreach ($metrics as $metric) {
            $this->metricRepository->remove($metric);
        }
    }
}
