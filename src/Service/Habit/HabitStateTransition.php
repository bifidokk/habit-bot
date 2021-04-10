<?php

declare(strict_types=1);

namespace App\Service\Habit;

use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\Enum;

class HabitStateTransition extends Enum
{
    use AutoDiscoveredValuesTrait;

    public const PUBLISH = 'publish';
}
