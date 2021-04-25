<?php

declare(strict_types=1);

namespace App\Service\Habit;

use App\Entity\Habit;
use App\Repository\HabitRepository;
use App\Service\Keyboard\HabitRemindDayInlineKeyboard;

class RemindDayService
{
    private const ALL_DAYS_MARKED_INT = 127;

    private HabitRepository $habitRepository;

    public function __construct(HabitRepository $habitRepository)
    {
        $this->habitRepository = $habitRepository;
    }

    public function toggleDay(Habit $habit, int $dayNumber): void
    {
        $remindDays = $this->getRemindDayArray($habit);

        foreach ($remindDays as $number => $day) {
            if ($number === $dayNumber) {
                $remindDays[$number] = $remindDays[$number] === '1' ? '0' : '1';
            }
        }

        $remindDaysString = implode('', $remindDays);
        $habit->setRemindWeekDays((int) bindec($remindDaysString));

        $this->habitRepository->save($habit);
    }

    public function markAll(Habit $habit): void
    {
        $habit->setRemindWeekDays(self::ALL_DAYS_MARKED_INT);
        $this->habitRepository->save($habit);
    }

    public function getRemindDaysAsString(Habit $habit): string
    {
        $remindDays = $this->getRemindDayArray($habit);
        $remindDayNames = [];

        foreach ($remindDays as $number => $day) {
            if ($day === 1) {
                $remindDayNames[] = HabitRemindDayInlineKeyboard::WEEK_DAYS[$number];
            }
        }

        return implode(', ', $remindDayNames);
    }

    private function getRemindDayArray(Habit $habit): array
    {
        return str_split(sprintf('%07d', decbin($habit->getRemindWeekDays())));
    }
}
