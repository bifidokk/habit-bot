<?php

declare(strict_types=1);

namespace App\Service\Habit\Dto;

class HabitResponseDto
{
    public function __construct(
        public string $id,
        public string $name,
        public array $days,
        public string $time,
        public string $createdAt,
        public array $completions = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'days' => $this->days,
            'time' => $this->time,
            'createdAt' => $this->createdAt,
            'completions' => $this->completions,
        ];
    }
}
