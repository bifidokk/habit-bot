<?php

declare(strict_types=1);

namespace App\Service\Habit;

use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\Enum;

class HabitState extends Enum
{
    use AutoDiscoveredValuesTrait;

    public const DRAFT = 'draft';
    public const PUBLISHED = 'published';
}
