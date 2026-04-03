<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Service\Command\CommandCallbackEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitRemindTimeInlineKeyboard
{
    private const MIN_HOUR = 6;

    private const MAX_HOUR = 23;

    private const BUTTONS_IN_A_ROW = 3;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function generate(string $habitId): InlineKeyboardMarkupType
    {
        $buttons = [];
        $count = 0;

        for ($i = self::MIN_HOUR; $i <= self::MAX_HOUR; $i++, $count++) {
            $rowNumber = floor($count / self::BUTTONS_IN_A_ROW);
            $timeLabel = sprintf('%02d:00', $i);

            $buttons[$rowNumber][] = InlineKeyboardButtonType::create(
                $timeLabel,
                [
                    'callbackData' => sprintf(
                        '%s?id=%s&time=%s',
                        CommandCallbackEnum::SetHabitRemindTime->value,
                        $habitId,
                        $timeLabel
                    ),
                ]
            );
        }

        $buttons[] = [
            InlineKeyboardButtonType::create(
                sprintf('%s %s', EmojiCode::Back->value, $this->translator->trans('back')),
                [
                    'callbackData' => sprintf(
                        '%s?id=%s',
                        CommandCallbackEnum::BackToRemindDay->value,
                        $habitId
                    ),
                ]
            ),
            InlineKeyboardButtonType::create(
                sprintf('%s %s', EmojiCode::Cancel->value, $this->translator->trans('cancel')),
                [
                    'callbackData' => sprintf(
                        '%s?id=%s',
                        CommandCallbackEnum::CancelHabitCreation->value,
                        $habitId
                    ),
                ]
            ),
        ];

        return InlineKeyboardMarkupType::create($buttons);
    }
}
