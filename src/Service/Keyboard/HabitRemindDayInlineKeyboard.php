<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Service\Command\CommandCallbackEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitRemindDayInlineKeyboard
{
    public const ALL_BUTTON = 'all';

    public const NEXT_BUTTON = 'next';

    public const WEEK_DAYS = [
        'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun',
    ];

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function generate(int $chosenWeekDays, string $habitId): InlineKeyboardMarkupType
    {
        $buttons = [];
        $chosenWeekDaysBin = sprintf('%07d', decbin($chosenWeekDays));

        foreach (self::WEEK_DAYS as $number => $day) {
            $dayLabel = $this->translator->trans(strtolower(sprintf('weekday.%s', $day)));

            if ($this->dayIsChosen($chosenWeekDaysBin, $number)) {
                $dayLabel = sprintf(
                    '%s%s',
                    EmojiCode::Marked->value,
                    $this->translator->trans(strtolower(sprintf('weekday.%s', $day)))
                );
            }

            $buttons[] = InlineKeyboardButtonType::create(
                $dayLabel,
                [
                    'callbackData' => sprintf(
                        '%s?id=%s&day=%s',
                        CommandCallbackEnum::SetHabitRemindDay->value,
                        $habitId,
                        $day
                    ),
                ]
            );
        }

        return InlineKeyboardMarkupType::create([
            $buttons,
            [
                InlineKeyboardButtonType::create(
                    $this->translator->trans('choose_all'),
                    [
                        'callbackData' => sprintf(
                            '%s?id=%s&day=%s',
                            CommandCallbackEnum::SetHabitRemindDay->value,
                            $habitId,
                            self::ALL_BUTTON
                        ),
                    ]
                ),
            ],
            [
                InlineKeyboardButtonType::create(
                    $this->translator->trans('next'),
                    [
                        'callbackData' => sprintf(
                            '%s?id=%s&day=%s',
                            CommandCallbackEnum::SetHabitRemindDay->value,
                            $habitId,
                            self::NEXT_BUTTON
                        ),
                    ]
                ),
            ],
        ]);
    }

    private function dayIsChosen(string $chosenWeekDaysBin, int $dayNumberInWeek): bool
    {
        return isset($chosenWeekDaysBin[$dayNumberInWeek]) && (int) $chosenWeekDaysBin[$dayNumberInWeek] === 1;
    }
}
