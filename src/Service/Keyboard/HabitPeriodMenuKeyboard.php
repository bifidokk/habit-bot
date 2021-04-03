<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use TgBotApi\BotApiBase\Type\KeyboardButtonType;
use TgBotApi\BotApiBase\Type\ReplyKeyboardMarkupType;

class HabitPeriodMenuKeyboard
{
    public const MARK_CODE = 'U+2705';

    private const WEEK_DAYS = [
        'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sar',
    ];

    public static function generate(int $chosenWeekDays): ReplyKeyboardMarkupType
    {
        $buttons = [];
        $chosenWeekDaysBin = sprintf( "%07d", decbin($chosenWeekDays));

        foreach (self::WEEK_DAYS as $number => $day) {
            if (self::dayIsChosen($chosenWeekDaysBin, $number)) {
                $day = sprintf('U+2705%s', $day);
            }

            $buttons[] = KeyboardButtonType::create($day);
        }

        return ReplyKeyboardMarkupType::create([
            $buttons,
            [KeyboardButtonType::create('Choose all')],
            [KeyboardButtonType::create('Back')],
        ]);
    }

    private static function dayIsChosen(string $chosenWeekDaysBin, $dayNumberInWeek): bool
    {
        return isset($chosenWeekDaysBin[$dayNumberInWeek]) && (int)$chosenWeekDaysBin[$dayNumberInWeek] === 1;
    }
}
