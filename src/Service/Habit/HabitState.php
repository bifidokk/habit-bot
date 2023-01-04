<?php

declare(strict_types=1);

namespace App\Service\Habit;

enum HabitState: string
{
    case Draft = 'draft';

    case Published = 'published';
}
