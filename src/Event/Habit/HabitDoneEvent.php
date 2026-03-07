<?php

declare(strict_types=1);

namespace App\Event\Habit;

use App\Entity\Habit;

class HabitDoneEvent
{
    public function __construct(
        private readonly Habit $habit,
        private readonly \DateTimeImmutable $date,
    ) {
    }

    public function getHabit(): Habit
    {
        return $this->habit;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }
}
