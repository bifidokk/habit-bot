<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Entity\Habit;
use App\Service\Command\CommandCallback;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitInlineKeyboard
{
    public const MARKED_CODE = "✅";
    public const UNMARKED_CODE = "☑️";
    public const PREVIEW_CODE = "👀️";

    public const STEPS = [
        CommandCallback::HABIT_DESCRIPTION_FORM => 'Add habit\'s description',
        CommandCallback::HABIT_REMIND_DAY_FORM => 'Add habit\'s remind day',
        CommandCallback::HABIT_REMIND_TIME_FORM => 'Add habit\'s remind time',
        CommandCallback::HABIT_PREVIEW => 'Preview',
    ];

    public static function generate(Habit $habit): InlineKeyboardMarkupType
    {
        $steps = [];

        foreach (self::STEPS as $step => $description) {
            $icon = self::isStepButtonMarked($step, $habit) ? self::MARKED_CODE : self::UNMARKED_CODE;

            if ($step === CommandCallback::HABIT_PREVIEW) {
                $icon = self::PREVIEW_CODE;
            }

            $steps[] = [InlineKeyboardButtonType::create(
                sprintf('%s%s', $icon, $description),
                ['callbackData' => $step]
            )];
        }

        return InlineKeyboardMarkupType::create($steps);
    }

    private static function isStepButtonMarked(string $step, Habit $habit): bool
    {
        if ($habit === null) {
            return false;
        }

        switch ($step) {
            case CommandCallback::HABIT_DESCRIPTION_FORM:
               return $habit->getDescription() !== '';
            case CommandCallback::HABIT_REMIND_DAY_FORM:
                return (int)$habit->getRemindWeekDays() > 0;
            case CommandCallback::HABIT_REMIND_TIME_FORM:
                return $habit->getRemindAt() instanceof \DateTimeImmutable;
            default:
                return false;
        }
    }
}
