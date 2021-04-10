<?php

declare(strict_types=1);

namespace App\Service\Habit;

use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\Enum;

class CreationHabitStateTransition extends Enum
{
    use AutoDiscoveredValuesTrait;

    public const PERIOD_ADDED = 'period_added';
}
