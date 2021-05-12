<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\Enum;

class EmojiCode extends Enum
{
    use AutoDiscoveredValuesTrait;

    public const ALARM = '⏰';
    public const SETTINGS = '🛠️';
    public const MARKED = '✅';
    public const UNMARKED = '☑️';
    public const PREVIEW = '👀️';
    public const BACK = '⬅️';
    public const PLUS = '➕️';
    public const CLOCKS = '🕒';
}
