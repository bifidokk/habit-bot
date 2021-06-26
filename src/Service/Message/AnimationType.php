<?php

declare(strict_types=1);

namespace App\Service\Message;

use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\Enum;

class AnimationType extends Enum
{
    use AutoDiscoveredValuesTrait;

    public const SUCCESS = 'success';
    public const TIMEZONE = 'timezone';
    public const LANGUAGE = 'language';
    public const NOT_REMOVED = 'not_removed';
    public const REMOVED = 'removed';
    public const HABIT_DONE = 'habit_done';
    public const HABIT_BUSY = 'habit_busy';
}
