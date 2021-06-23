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
    public const NEXT = '➡️️';
    public const PLUS = '➕️';
    public const CLOCKS = '🕒';
    public const WORLD = '🌎';
    public const ENGLISH = '🇬🇧';
    public const RUSSIAN = '🇷🇺';
    public const LIST = '📋';
    public const REMOVE = '🗑️';
    public const DEVIL = '😈';
    public const ANGEL = '😇';
    public const BUSY = '😩';
}
