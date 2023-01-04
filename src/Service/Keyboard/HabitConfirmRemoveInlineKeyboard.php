<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Entity\Habit;
use App\Service\Command\CommandCallbackEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitConfirmRemoveInlineKeyboard
{
    public function __construct(private TranslatorInterface $translator) {}

    public function generate(Habit $habit): InlineKeyboardMarkupType
    {
        return InlineKeyboardMarkupType::create([
            [
                InlineKeyboardButtonType::create(sprintf(
                    '️%s %s',
                    EmojiCode::Devil->value,
                    $this->translator->trans('yes')
                ), [
                    'callbackData' => sprintf(
                        '%s?id=%s&c=1',
                        CommandCallbackEnum::HabitRemove->value,
                        $habit->getId()
                    ),
                ]),
                InlineKeyboardButtonType::create(sprintf(
                    '️%s %s',
                    EmojiCode::Angel->value,
                    $this->translator->trans('no')
                ), [
                    'callbackData' => sprintf(
                        '%s?id=%s&c=0',
                        CommandCallbackEnum::HabitRemove->value,
                        $habit->getId()
                    ),
                ]),
            ],
        ]);
    }
}
