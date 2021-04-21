<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Service\Command\CommandCallback;
use App\Service\Habit\HabitDto;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitInlineKeyboard
{
    private const MARKED_CODE = "✅";
    private const UNMARKED_CODE = "☑️";

    private const STEPS = [
        CommandCallback::HABIT_DESCRIPTION_FORM => 'Add habit\'s description',
        '/setHabitRemindDay' => 'Add habit\'s remind day',
        '/setHabitRemindTime' => 'Add habit\'s remind time',
        '/habitPreview' => 'Preview',
    ];

    public static function generate(HabitDto $habit): InlineKeyboardMarkupType
    {
        $steps = [];

        foreach (self::STEPS as $step => $description) {
            $icon = self::isStepButtonMarked($step, $description, $habit) ? self::MARKED_CODE : self::UNMARKED_CODE;

            $steps[] = [InlineKeyboardButtonType::create(
                sprintf('%s%s', $icon, $description),
                ['callbackData' => $step]
            )];
        }

        return InlineKeyboardMarkupType::create($steps);
    }

    private static function isStepButtonMarked(string $step, string $description, HabitDto $habit): bool
    {
        if ($habit === null) {
            return false;
        }

        switch ($step) {
            case 'habit_description':
               return $habit->description !== null;
            case 'habit_remind_day':
                return (int)$habit->remindDay > 0;
            case 'habit_remind_time':
                return $habit->remindAt instanceof \DateTimeImmutable;
            default:
                return false;
        }
    }
}
