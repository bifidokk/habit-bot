<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use TgBotApi\BotApiBase\Type\KeyboardButtonType;
use TgBotApi\BotApiBase\Type\ReplyKeyboardMarkupType;

class HabitRemindTimeKeyboard
{
    private const MIN_HOUR = 6;
    private const MAX_HOUR = 23;
    private const BUTTONS_IN_A_ROW = 6;
    private const BACK_BUTTON_LABEL = 'Back';

    public static function generate(): ReplyKeyboardMarkupType
    {
        $buttons = [];
        $count = 0;

        for ($i = self::MIN_HOUR; $i <= self::MAX_HOUR; $i++, $count++) {
            $rowNumber = floor($count / self::BUTTONS_IN_A_ROW);
            $buttons[$rowNumber][] = KeyboardButtonType::create(
                sprintf('%02d:00', $i)
            );
        }

        $rowNumber = floor($count / self::BUTTONS_IN_A_ROW);
        $buttons[$rowNumber] = [KeyboardButtonType::create(self::BACK_BUTTON_LABEL)];

        return ReplyKeyboardMarkupType::create($buttons);
    }
}
