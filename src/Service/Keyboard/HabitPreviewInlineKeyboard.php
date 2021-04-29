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
                    'callbackData' => sprintf(
                        '%s?id=%s',
                        CommandCallbackEnum::HABIT_FORM,
                        $habit->getId()->toRfc4122()
                    ),
                ]),
                InlineKeyboardButtonType::create('✅Submit', [
                    'callbackData' => sprintf(
                        '%s?id=%s',
                        CommandCallbackEnum::HABIT_PUBLISH,
                        $habit->getId()->toRfc4122()
                    ),
                ]),
            ],
        ]);
    }
}
