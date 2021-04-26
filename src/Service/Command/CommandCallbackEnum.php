<?php

declare(strict_types=1);

namespace App\Service\Command;

use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\Enum;

class CommandCallbackEnum extends Enum
{
    use AutoDiscoveredValuesTrait;

    public const HABIT_FORM = '/formHabit';
    public const HABIT_DESCRIPTION_FORM = '/habitDescription';
    public const SET_HABIT_DESCRIPTION = '/setDescription';
    public const HABIT_REMIND_DAY_FORM = '/habitRemindDay';
    public const SET_HABIT_REMIND_DAY = '/setRemindDay';
    public const HABIT_REMIND_TIME_FORM = '/habitRemindTime';
    public const SET_HABIT_REMIND_TIME = '/setRemindTime';
    public const HABIT_PREVIEW = '/habitPreview';
    public const HABIT_PUBLISH = '/habitPublish';
}
