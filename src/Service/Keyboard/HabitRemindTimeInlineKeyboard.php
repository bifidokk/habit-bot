<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Service\Command\CommandCallbackEnum;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitRemindTimeInlineKeyboard
{
    private const MIN_HOUR = 6;
    private const MAX_HOUR = 23;
    private const BUTTONS_IN_A_ROW = 6;

    public static function generate(string $habitId): InlineKeyboardMarkupType
    {
        $buttons = [];
        $count = 0;

        for ($i = self::MIN_HOUR; $i <= self::MAX_HOUR; $i++, $count++) {
            $rowNumber = floor($count / self::BUTTONS_IN_A_ROW);
            $timeLabel = sprintf('%02d:00', $i);

            $buttons[$rowNumber][] = InlineKeyboardButtonType::create(
                $timeLabel,
                [
                    'callbackData' => sprintf(
                        '%s?id=%stime=%s',
                        CommandCallbackEnum::SET_HABIT_REMIND_TIME,
                        $habitId,
                        $timeLabel
                    ),
                ]
            );
        }

        return InlineKeyboardMarkupType::create($buttons);
    }
}
