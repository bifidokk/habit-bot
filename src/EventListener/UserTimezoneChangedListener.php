<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Habit;
use App\Service\Habit\HabitService;
use App\Service\User\Event\TimezoneChangedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserTimezoneChangedListener implements EventSubscriberInterface
{
    private HabitService $habitService;

    public function __construct(
        HabitService $habitService
    ) {
        $this->habitService = $habitService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TimezoneChangedEvent::class => 'updateUserHabitsRemindTime',
        ];
    }

    public function updateUserHabitsRemindTime(TimezoneChangedEvent $event): void
    {
        $user = $event->getUser();
        $habits = $user->getPublishedHabits();

        if (count($habits) === 0) {
            return;
        }

        /** @var Habit $habit */
        foreach ($habits as $habit) {
            if (!$habit->isPublished()) {
                continue;
            }

            $this->habitService->updateHabitNextRemindTime($habit);
        }
    }
}
