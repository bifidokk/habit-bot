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
}
