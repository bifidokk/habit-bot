<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

enum EmojiCode: string
{
    case Alarm = '⏰';
    case Settings = '🛠️';
    case Marked = '✅';
    case Unmarked = '☑️';
    case Preview = '👀️';
    case Back = '⬅️';
    case Next = '➡️️';
    case Plus = '➕️';
    case Clocks = '🕒';
    case World = '🌎';
    case English = '🇬🇧';
    case Russian = '🇷🇺';
    case List = '📋';
    case Remove = '🗑️';
    case Devil = '😈';
    case Angel = '😇';
    case Busy = '😩';
}
