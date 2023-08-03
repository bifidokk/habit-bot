<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\Habit\HabitDoneEvent;
use App\Service\Metric\MetricService;
use App\Service\Metric\MetricType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HabitDoneSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MetricService $metricService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            HabitDoneEvent::class => 'updateHabitDoneMetric',
        ];
    }

    public function updateHabitDoneMetric(HabitDoneEvent $habitDoneEvent): void
    {
        $habit = $habitDoneEvent->getHabit();
        $this->metricService->addHabitMetric(
            MetricType::HabitDone,
            new \DateTimeImmutable(),
            $habit
        );
    }
}
