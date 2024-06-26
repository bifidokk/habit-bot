<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Service\Command\CommandCallbackEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitMenuInlineKeyboard
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function generate(array $habits): InlineKeyboardMarkupType
    {
        $buttons = [
            [InlineKeyboardButtonType::create(
                sprintf(
                    '%s%s',
                    EmojiCode::Plus->value,
                    $this->translator->trans('habit.menu.add_new_habit')
                ),
                [
                    'callbackData' => CommandCallbackEnum::HabitForm->value,
                ]
            )],
        ];

        if (count($habits) > 0) {
            $buttons[] = [InlineKeyboardButtonType::create(
                sprintf(
                    '%s%s',
                    EmojiCode::List->value,
                    $this->translator->trans('habit.menu.my_habits')
                ),
                [
                    'callbackData' => CommandCallbackEnum::HabitList->value,
                ]
            )];
        }

        return InlineKeyboardMarkupType::create($buttons);
    }
}
