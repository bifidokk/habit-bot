<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Service\Command\CommandCallbackEnum;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitRemindDayInlineKeyboard
{
    public const CHOOSE_ALL_BUTTON_LABEL = 'Choose all';
    public const NEXT_BUTTON_LABEL = 'Next';

    public const WEEK_DAYS = [
        'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat',
    ];

    public static function generate(int $chosenWeekDays, string $habitId): InlineKeyboardMarkupType
    {
        $buttons = [];
        $chosenWeekDaysBin = sprintf('%07d', decbin($chosenWeekDays));

        foreach (self::WEEK_DAYS as $number => $day) {
            $dayLabel = $day;

            if (self::dayIsChosen($chosenWeekDaysBin, $number)) {
                $dayLabel = sprintf('%s%s', EmojiCode::MARKED, $day);
            }

            $buttons[] = InlineKeyboardButtonType::create(
                $dayLabel,
                [
                    'callbackData' => sprintf(
                        '%s?id=%s&day=%s',
                        CommandCallbackEnum::SET_HABIT_REMIND_DAY,
                        $habitId,
                        $day
                    ),
                ]
            );
        }

        return InlineKeyboardMarkupType::create([
            $buttons,
            [
                InlineKeyboardButtonType::create(
                    self::CHOOSE_ALL_BUTTON_LABEL,
                    [
                        'callbackData' => sprintf(
                            '%s?id=%s&day=%s',
                            CommandCallbackEnum::SET_HABIT_REMIND_DAY,
                            $habitId,
                            self::CHOOSE_ALL_BUTTON_LABEL
                        ),
                    ]
                ),
            ],
            [
                InlineKeyboardButtonType::create(
                    self::NEXT_BUTTON_LABEL,
                    [
                        'callbackData' => sprintf(
                            '%s?id=%s&day=%s',
                            CommandCallbackEnum::SET_HABIT_REMIND_DAY,
                            $habitId,
                            self::NEXT_BUTTON_LABEL
                        ),
                    ]
                ),
            ],
        ]);
    }

    private static function dayIsChosen(string $chosenWeekDaysBin, int $dayNumberInWeek): bool
    {
        return isset($chosenWeekDaysBin[$dayNumberInWeek]) && (int) $chosenWeekDaysBin[$dayNumberInWeek] === 1;
    }
}
