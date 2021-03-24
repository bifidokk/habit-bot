<?php

declare(strict_types=1);

namespace App\Service\Command;

use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\Enum;

class CommandPriority extends Enum
{
    use AutoDiscoveredValuesTrait;

    public const HIGH = 1;
    public const MEDIUM = 0;
    public const LOW = -1;
}
