<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use TgBotApi\BotApiBase\Type\KeyboardButtonType;
use TgBotApi\BotApiBase\Type\ReplyKeyboardMarkupType;

class HabitPeriodMenuKeyboard
{
    public static function generate(): ReplyKeyboardMarkupType
    {
        return ReplyKeyboardMarkupType::create([
            [
                KeyboardButtonType::create('Sun'),
                KeyboardButtonType::create('Mon'),
                KeyboardButtonType::create('Tue'),
                KeyboardButtonType::create('Wed'),
                KeyboardButtonType::create('Thu'),
                KeyboardButtonType::create('Fri'),
                KeyboardButtonType::create('Sar'),
            ],
            [KeyboardButtonType::create('Choose all')],
            [KeyboardButtonType::create('Back')],
        ]);
    }
}
