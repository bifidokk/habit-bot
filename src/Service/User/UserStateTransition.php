<?php

declare(strict_types=1);

namespace App\Service\User;

use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\Enum;

class UserStateTransition extends Enum
{
    use AutoDiscoveredValuesTrait;

    public const NEW_CUSTOM_HABIT = 'new_habit';
}
