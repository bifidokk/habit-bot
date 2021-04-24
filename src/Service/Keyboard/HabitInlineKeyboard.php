<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Entity\Habit;
use App\Service\Command\CommandCallbackEnum;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitInlineKeyboard
{
    public const MARKED_CODE = '✅';
    public const UNMARKED_CODE = '☑️';
    public const PREVIEW_CODE = '👀️';

    public const STEPS = [
        CommandCallbackEnum::HABIT_DESCRIPTION_FORM => 'Add habit\'s description',
        CommandCallbackEnum::HABIT_REMIND_DAY_FORM => 'Add habit\'s remind day',
        CommandCallbackEnum::HABIT_REMIND_TIME_FORM => 'Add habit\'s remind time',
        CommandCallbackEnum::HABIT_PREVIEW => 'Preview',
    ];

    public static function generate(Habit $habit): InlineKeyboardMarkupType
    {
        $steps = [];

        foreach (self::STEPS as $step => $description) {
            $icon = self::isStepButtonMarked($step, $habit) ? self::MARKED_CODE : self::UNMARKED_CODE;

            if ($step === CommandCallbackEnum::HABIT_PREVIEW) {
                $icon = self::PREVIEW_CODE;
            }

            $steps[] = [InlineKeyboardButtonType::create(
                sprintf('%s%s', $icon, $description),
                [
                    'callbackData' => $step,
                ]
            )];
        }

        return InlineKeyboardMarkupType::create($steps);
    }

    private static function isStepButtonMarked(string $step, Habit $habit): bool
    {
        switch ($step) {
            case CommandCallbackEnum::HABIT_DESCRIPTION_FORM:
               return $habit->getDescription() !== '';

            case CommandCallbackEnum::HABIT_REMIND_DAY_FORM:
                return (int) $habit->getRemindWeekDays() > 0;

            case CommandCallbackEnum::HABIT_REMIND_TIME_FORM:
                return $habit->getRemindAt() instanceof \DateTimeImmutable;

            default:
                return false;
        }
    }
}
