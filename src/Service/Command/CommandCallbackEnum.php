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
    public const HABIT_REMIND_DAY_FORM = '/habitDay';
    public const SET_HABIT_REMIND_DAY = '/setDay';
    public const HABIT_REMIND_TIME_FORM = '/habitTime';
    public const SET_HABIT_REMIND_TIME = '/setTime';
    public const HABIT_PREVIEW = '/habitPreview';
    public const HABIT_PUBLISH = '/habitPublish';
    public const SETTINGS_TIMEZONE_FORM = '/settingsTimezone';
    public const SET_TIMEZONE = '/setTimezone';
    public const SETTINGS_LANGUAGE_FORM = '/settingsLanguage';
    public const SET_LANGUAGE = '/setLanguage';
    public const HABIT_LIST = '/listHabit';
    public const HABIT_REMOVE_CONFIRM = '/removeConfirm';
    public const HABIT_REMOVE = '/remove';
    public const HABIT_DONE = '/done';
    public const HABIT_BUSY = '/busy';
}
