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
        'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat',
    ];

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function generate(int $chosenWeekDays, string $habitId): InlineKeyboardMarkupType
    {
        $buttons = [];
        $chosenWeekDaysBin = sprintf('%07d', decbin($chosenWeekDays));

        foreach (self::WEEK_DAYS as $number => $day) {
            $dayLabel = $day;

            if ($this->dayIsChosen($chosenWeekDaysBin, $number)) {
                $dayLabel = sprintf(
                    '%s%s',
                    EmojiCode::MARKED,
                    $this->translator->trans(strtolower(sprintf('weekday.%s', $day)))
                );
            }

            $buttons[] = InlineKeyboardButtonType::create(
                $dayLabel,
                [
                    'callbackData' => sprintf(
                        '%s?id=%s&day=%s',
                        CommandCallbackEnum::SET_HABIT_REMIND_DAY,
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
                            CommandCallbackEnum::SET_HABIT_REMIND_DAY,
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
                            CommandCallbackEnum::SET_HABIT_REMIND_DAY,
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
