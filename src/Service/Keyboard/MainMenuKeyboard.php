<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use TgBotApi\BotApiBase\Type\KeyboardButtonType;
use TgBotApi\BotApiBase\Type\ReplyKeyboardMarkupType;

class MainMenuKeyboard
{
    public static function generate(): ReplyKeyboardMarkupType
    {
        return ReplyKeyboardMarkupType::create([
            [KeyboardButtonType::create('Add a new habit')],
        ]);
    }
}