<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Entity\Habit;
use App\Service\Command\CommandCallbackEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitInlineKeyboard
{
    public function __construct(private TranslatorInterface $translator) {}

    public function generate(Habit $habit): InlineKeyboardMarkupType
    {
        $steps = [];

        foreach ($this->getSteps() as $step => $description) {
            $icon = $this->isStepButtonMarked($step, $habit) ? EmojiCode::MARKED : EmojiCode::UNMARKED;

            if ($step === CommandCallbackEnum::HABIT_PREVIEW) {
                $icon = EmojiCode::PREVIEW;
            }

            $steps[] = [InlineKeyboardButtonType::create(
                sprintf('%s%s', $icon, $description),
                [
                    'callbackData' => sprintf('%s?%s', $step, $habit->getQueryParameter()),
                ]
            )];
        }

        return InlineKeyboardMarkupType::create($steps);
    }

    public function getSteps(): array
    {
        return [
            CommandCallbackEnum::HABIT_DESCRIPTION_FORM => $this->translator->trans('habit.creation.add_description'),
            CommandCallbackEnum::HABIT_REMIND_DAY_FORM => $this->translator->trans('habit.creation.add_remind_day'),
            CommandCallbackEnum::HABIT_REMIND_TIME_FORM => $this->translator->trans('habit.creation.add_remind_time'),
            CommandCallbackEnum::HABIT_PREVIEW => $this->translator->trans('preview'),
        ];
    }

    private function isStepButtonMarked(string $step, Habit $habit): bool
    {
        return match ($step) {
            CommandCallbackEnum::HABIT_DESCRIPTION_FORM => $habit->getDescription() !== '',
            CommandCallbackEnum::HABIT_REMIND_DAY_FORM => $habit->getRemindWeekDays() > 0,
            CommandCallbackEnum::HABIT_REMIND_TIME_FORM => $habit->getRemindAt() instanceof \DateTimeImmutable,
            default => false,
        };
    }
}
