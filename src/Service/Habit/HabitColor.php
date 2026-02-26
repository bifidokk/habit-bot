<?php

declare(strict_types=1);

namespace App\Service\Habit;

enum HabitColor: string
{
    case Red = '#ef4444';
    case Orange = '#f97316';
    case Yellow = '#eab308';
    case Lime = '#84cc16';
    case Green = '#22c55e';
    case Teal = '#14b8a6';
    case Cyan = '#06b6d4';
    case Blue = '#3b82f6';
    case Violet = '#8b5cf6';
    case Purple = '#a855f7';
    case Pink = '#ec4899';
    case Rose = '#f43f5e';
    case Slate = '#64748b';

    public const DEFAULT = self::Violet;
}
