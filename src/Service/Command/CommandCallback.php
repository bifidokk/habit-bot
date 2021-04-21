<?php

declare(strict_types=1);

namespace App\Service\Command;

use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\Enum;

class CommandCallback extends Enum
{
    use AutoDiscoveredValuesTrait;

    public const HABIT_DESCRIPTION_FORM = '/formHabitDescription';
    public const SET_HABIT_DESCRIPTION = '/setHabitDescription';
}
