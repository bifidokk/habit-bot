<?php

declare(strict_types=1);

namespace App\Event\Habit;

use App\Entity\Habit;

class HabitDoneEvent
{
    public function __construct(
        private Habit $habit,
    ) {}

    public function getHabit(): Habit
    {
        return $this->habit;
    }
}