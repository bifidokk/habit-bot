<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Entity\Habit;
use App\Service\Command\CommandCallbackEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitViewInlineKeyboard
{
    public function __construct(private TranslatorInterface $translator) {}

    public function generate(Habit $habit, int $page, bool $showNext): InlineKeyboardMarkupType
    {
        $buttons = [];

        if ($page > 0) {
            $buttons[] =
                InlineKeyboardButtonType::create(sprintf(
                    '️%s %s',
                    EmojiCode::Back->value,
                    $this->translator->trans('previous')
                ), [
                    'callbackData' => sprintf(
                        '%s?page=%s',
                        CommandCallbackEnum::HabitList->value,
                        $page - 1
                    ),
                ]);
        }

        $buttons[] =
            InlineKeyboardButtonType::create(sprintf(
                '️%s %s',
                EmojiCode::Remove->value,
                $this->translator->trans('remove')
            ), [
                'callbackData' => sprintf(
                    '%s?id=%s',
                    CommandCallbackEnum::HabitRemoveConfirm->value,
                    $habit->getId()
                ),
            ]);

        if ($showNext === true) {
            $buttons[] =
                InlineKeyboardButtonType::create(sprintf(
                    '️%s %s',
                    EmojiCode::Next->value,
                    $this->translator->trans('next')
                ), [
                    'callbackData' => sprintf(
                        '%s?page=%s',
                        CommandCallbackEnum::HabitList->value,
                        $page + 1
                    ),
                ]);
        }

        return InlineKeyboardMarkupType::create([$buttons]);
    }
}
