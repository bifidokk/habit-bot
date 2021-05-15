<?php

declare(strict_types=1);

namespace App\Service\Habit;

use App\Entity\Habit;
use App\Repository\HabitRepository;
use App\Service\Keyboard\HabitRemindDayInlineKeyboard;

class RemindService
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
            if ((int) $day === 1) {
                $remindDayNames[] = HabitRemindDayInlineKeyboard::WEEK_DAYS[$number];
            }
        }

        return implode(', ', $remindDayNames);
    }

    public function getNextRemindTime(
        \DateTimeImmutable $currentTime,
        Habit $habit
    ): \DateTimeImmutable {
        $remindDays = $this->getRemindDayArray($habit);
        $nextRemindTime = [];
        $user = $habit->getUser();

        foreach ($remindDays as $number => $day) {
            if ((int) $day === 0) {
                continue;
            }

            $remindTime = new \DateTimeImmutable(
                sprintf(
                    '%s this week %s',
                    HabitRemindDayInlineKeyboard::WEEK_DAYS[$number],
                    $habit->getRemindAt()->format('H:i')
                ), $user->getTimezone()
            );

            $offset = -$remindTime->getOffset();
            $remindTime = $remindTime->modify(sprintf('%s seconds', $offset));

            if ($remindTime >= $currentTime) {
                $nextRemindTime[] = $remindTime;
            }

            // add next week if there are not days in this week (for example today is the end of the week)
            $remindTimeNextWeek = new \DateTimeImmutable(
                sprintf(
                    '%s next week %s',
                    HabitRemindDayInlineKeyboard::WEEK_DAYS[$number],
                    $habit->getRemindAt()->format('H:i')
                ), $user->getTimezone()
            );

            $remindTimeNextWeek = $remindTimeNextWeek->modify(sprintf('%s seconds', $offset));

            if ($remindTimeNextWeek >= $currentTime) {
                $nextRemindTime[] = $remindTimeNextWeek;
            }
        }

        usort($nextRemindTime, function ($a, $b) {
            return $a <=> $b;
        });

        return array_shift($nextRemindTime);
    }

    private function getRemindDayArray(Habit $habit): array
    {
        return str_split(sprintf('%07d', decbin($habit->getRemindWeekDays())));
    }
}
