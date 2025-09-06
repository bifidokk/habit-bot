<?php

declare(strict_types=1);

namespace App\Service\Habit\Factory;

use App\Entity\Habit;
use App\Service\Habit\Dto\HabitResponseDto;
use App\Service\Habit\HabitCompletion\HabitCompletionGenerator;

class HabitResponseDtoFactory
{
    public function __construct(
        private readonly HabitCompletionGenerator $habitCompletionGenerator,
    ) {
    }

    public function createFromEntity(Habit $habit): HabitResponseDto
    {
        $completions = $this->habitCompletionGenerator->generateCompletions($habit);

        return new HabitResponseDto(
            id: $habit->getId()?->toRfc4122() ?? '',
            name: $habit->getDescription(),
            days: $habit->getRemindWeekDaysArray(),
            time: $habit->getRemindAt()?->format('H:i') ?? '00:00',
            createdAt: $habit->getCreatedAt()->format('Y-m-d\TH:i:s\Z'),
            completions: $completions,
        );
    }

    /**
     * @param Habit[] $habits
     * @return HabitResponseDto[]
     */
    public function createFromEntities(array $habits): array
    {
        return array_map(fn (Habit $habit) => $this->createFromEntity($habit), $habits);
    }
}
