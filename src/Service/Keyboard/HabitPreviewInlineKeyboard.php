<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Entity\Habit;
use App\Service\Command\CommandCallbackEnum;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitPreviewInlineKeyboard
{
    public static function generate(Habit $habit): InlineKeyboardMarkupType
    {
        return InlineKeyboardMarkupType::create([
            [
                InlineKeyboardButtonType::create('⬅️Back', [
                    'callbackData' => CommandCallbackEnum::HABIT_FORM,
                ]),
                InlineKeyboardButtonType::create('✅Submit', [
                    'callbackData' => CommandCallbackEnum::HABIT_PUBLISH,
                ]),
            ],
        ]);
    }
}
