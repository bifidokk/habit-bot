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
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function generate(Habit $habit): InlineKeyboardMarkupType
    {
        return InlineKeyboardMarkupType::create([
            [
                InlineKeyboardButtonType::create(sprintf(
                    '️%s %s',
                    EmojiCode::DEVIL,
                    $this->translator->trans('yes')
                ), [
                    'callbackData' => sprintf(
                        '%s?id=%s&c=1',
                        CommandCallbackEnum::HABIT_REMOVE,
                        $habit->getId()
                    ),
                ]),
                InlineKeyboardButtonType::create(sprintf(
                    '️%s %s',
                    EmojiCode::ANGEL,
                    $this->translator->trans('no')
                ), [
                    'callbackData' => sprintf(
                        '%s?id=%s&c=0',
                        CommandCallbackEnum::HABIT_REMOVE,
                        $habit->getId()
                    ),
                ]),
            ],
        ]);
    }
}
