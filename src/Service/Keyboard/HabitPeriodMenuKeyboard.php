<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use TgBotApi\BotApiBase\Type\KeyboardButtonType;
use TgBotApi\BotApiBase\Type\ReplyKeyboardMarkupType;

class HabitPeriodMenuKeyboard
{
    public const MARK_CODE = "\xE2\x9C\x85";
    public const CHOOSE_ALL_BUTTON_LABEL = 'Choose all';
    public const NEXT_BUTTON_LABEL = 'Next';

    public const WEEK_DAYS = [
        'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat',
    ];

    public static function generate(int $chosenWeekDays): ReplyKeyboardMarkupType
    {
        $buttons = [];
        $chosenWeekDaysBin = sprintf('%07d', decbin($chosenWeekDays));

        foreach (self::WEEK_DAYS as $number => $day) {
            if (self::dayIsChosen($chosenWeekDaysBin, $number)) {
                $day = sprintf('%s%s', self::MARK_CODE, $day);
            }

            $buttons[] = KeyboardButtonType::create($day);
        }

        return ReplyKeyboardMarkupType::create([
            $buttons,
            [KeyboardButtonType::create(self::CHOOSE_ALL_BUTTON_LABEL)],
            [KeyboardButtonType::create('Back'), KeyboardButtonType::create(self::NEXT_BUTTON_LABEL)],
        ]);
    }

    private static function dayIsChosen(string $chosenWeekDaysBin, int $dayNumberInWeek): bool
    {
        return isset($chosenWeekDaysBin[$dayNumberInWeek]) && (int) $chosenWeekDaysBin[$dayNumberInWeek] === 1;
    }
}
