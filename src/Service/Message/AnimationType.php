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
}
