<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Service\Command\CommandCallbackEnum;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitMenuInlineKeyboard
{
    public static function generate(): InlineKeyboardMarkupType
    {
        return InlineKeyboardMarkupType::create([
            [InlineKeyboardButtonType::create(
                sprintf('%sAdd a new habit', EmojiCode::PLUS),
                [
                    'callbackData' => CommandCallbackEnum::HABIT_FORM,
                ]
            )],
        ]);
    }
}
