<?php

declare(strict_types=1);

namespace App\Service\Habit\HabitCompletion;

use App\Entity\Habit;
use App\Entity\User;
use App\Event\Habit\HabitDoneEvent;
use App\Service\Habit\Exception\CouldNotCreateHabitCompletion;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class HabitCompletionService
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function createHabitCompletion(
        Habit $habit,
        HabitCompleteRequest $habitCompleteRequest
    ): void {
        if (! $this->isToday(
            $habitCompleteRequest->getDateAsDateTimeImmutable(),
            $habit->getUser(),
        )) {
            throw new CouldNotCreateHabitCompletion();
        }

        if (! $this->canBeCompleted($habit)) {
            throw new CouldNotCreateHabitCompletion();
        }

        $this->eventDispatcher->dispatch(new HabitDoneEvent($habit));
    }

    public function isToday(\DateTimeImmutable $date, User $user): bool
    {
        $todayInUserTz = new \DateTime('now', $user->getTimezone());

        return $todayInUserTz->format('Y-m-d') === $date->format('Y-m-d');
    }

    public function canBeCompleted(Habit $habit): bool
    {
        if (! $habit->isPublished()) {
            return false;
        }

        return $this->canBeCompletedToday($habit);
    }

    public function canBeCompletedToday(Habit $habit): bool
    {
        $userTimezone = $habit->getUser()->getTimezone();

        $todayInUserTz = new \DateTime('now', $userTimezone);
        $currentDayOfWeek = (int) $todayInUserTz->format('w'); // 0 = Sunday, 1 = Monday, etc.

        $habitDayOfWeek = $this->convertPhpDayToHabitDay($currentDayOfWeek);
        $remindWeekDays = $habit->getRemindWeekDaysArray();

        return in_array($habitDayOfWeek, $remindWeekDays, true);
    }

    /**
     * Convert PHP's day of week (Sun=0, Mon=1, Tue=2, Wed=3, Thu=4, Fri=5, Sat=6)
     * to Habit's day of week (Mon=0, Tue=1, Wed=2, Thu=3, Fri=4, Sat=5, Sun=6)
     */
    private function convertPhpDayToHabitDay(int $phpDayOfWeek): int
    {
        return ($phpDayOfWeek + 6) % 7;
    }
}
