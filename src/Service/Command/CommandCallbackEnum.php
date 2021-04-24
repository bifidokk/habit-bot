<?php

declare(strict_types=1);

namespace App\Service\Command;

use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\Enum;

class CommandCallbackEnum extends Enum
{
    use AutoDiscoveredValuesTrait;

    public const HABIT_DESCRIPTION_FORM = '/formHabitDescription';
    public const SET_HABIT_DESCRIPTION = '/setHabitDescription';
    public const HABIT_REMIND_DAY_FORM = '/formHabitRemindDay';
    public const SET_HABIT_REMIND_DAY = '/setHabitRemindDay';
    public const HABIT_REMIND_TIME_FORM = '/formHabitRemindTime';
    public const SET_HABIT_REMIND_TIME = '/setHabitRemindTime';
    public const HABIT_PREVIEW = '/habitPreview';
}
