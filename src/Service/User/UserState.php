<?php

declare(strict_types=1);

namespace App\Service\User;

use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\Enum;

class UserState extends Enum
{
    use AutoDiscoveredValuesTrait;

    public const START = 'start';
    public const NEW_CUSTOM_HABIT = 'new_custom_habit';
}
