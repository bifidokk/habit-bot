<?php

declare(strict_types=1);

namespace App\Service\Habit;

class HabitDto
{
    public ?string $description = null;

    public ?int $remindDay = null;

    public ?\DateTimeImmutable $remindAt = null;
}
