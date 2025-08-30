<?php

declare(strict_types=1);

namespace App\Service\Habit\Dto;

trait HabitRequestTrait
{
    public function generateRemindWeekDaysInteger(): int
    {
        $week = [0, 0, 0, 0, 0, 0, 0];

        foreach ($this->days as $day) {
            if ($day >= 0 && $day <= 6) {
                $week[$day] = 1;
            }
        }

        $remindWeekDaysString = implode('', $week);

        return (int) bindec($remindWeekDaysString);
    }
}
