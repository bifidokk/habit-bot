<?php

declare(strict_types=1);

namespace App\Service\Habit\Factory;

use App\Entity\Habit;
use App\Service\Habit\Dto\HabitResponseDto;

class HabitResponseDtoFactory
{
    public function createFromEntity(Habit $habit): HabitResponseDto
    {
        return new HabitResponseDto(
            id: $habit->getId()->toRfc4122(),
            name: $habit->getDescription(),
            days: $habit->getRemindWeekDaysArray(),
            time: $habit->getRemindAt()->format('H:i'),
            createdAt: $habit->getCreatedAt()->format('Y-m-d'),
        );
    }

    /**
     * @param Habit[] $habits
     * @return HabitResponseDto[]
     */
    public function createFromEntities(array $habits): array
    {
        return array_map(fn(Habit $habit) => $this->createFromEntity($habit), $habits);
    }
}
