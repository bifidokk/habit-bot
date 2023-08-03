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
    public function __construct(private readonly TranslatorInterface $translator) {}

    public function generate(Habit $habit): InlineKeyboardMarkupType
    {
        $steps = [];

        foreach ($this->getSteps() as $step => $description) {
            $icon = $this->isStepButtonMarked($step, $habit) ? EmojiCode::Marked->value : EmojiCode::Unmarked->value;

            if ($step === CommandCallbackEnum::HabitPreview->value) {
                $icon = EmojiCode::Preview->value;
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
            CommandCallbackEnum::HabitDescriptionForm->value => $this->translator->trans('habit.creation.add_description'),
            CommandCallbackEnum::HabitRemindDayForm->value => $this->translator->trans('habit.creation.add_remind_day'),
            CommandCallbackEnum::HabitRemindTimeForm->value => $this->translator->trans('habit.creation.add_remind_time'),
            CommandCallbackEnum::HabitPreview->value => $this->translator->trans('preview'),
        ];
    }

    private function isStepButtonMarked(string $step, Habit $habit): bool
    {
        return match ($step) {
            CommandCallbackEnum::HabitDescriptionForm->value => $habit->getDescription() !== '',
            CommandCallbackEnum::HabitRemindDayForm->value => $habit->getRemindWeekDays() > 0,
            CommandCallbackEnum::HabitRemindTimeForm->value => $habit->getRemindAt() instanceof \DateTimeImmutable,
            default => false,
        };
    }
}
