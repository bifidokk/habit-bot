<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Service\Command\CommandCallbackEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitViewInlineKeyboard
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function generate(int $page, bool $showNext): InlineKeyboardMarkupType
    {
        $buttons = [];

        if ($page > 0) {
            $buttons[] =
                InlineKeyboardButtonType::create(sprintf(
                    '️%s%s',
                    EmojiCode::BACK,
                    $this->translator->trans('previous')
                ), [
                    'callbackData' => sprintf(
                        '%s?page=%s',
                        CommandCallbackEnum::HABIT_LIST,
                        $page - 1
                    ),
                ]);
        }

        $buttons[] =
            InlineKeyboardButtonType::create(sprintf(
                '️%s%s',
                EmojiCode::REMOVE,
                $this->translator->trans('remove')
            ), [
                'callbackData' => sprintf(
                    '%s',
                    CommandCallbackEnum::HABIT_REMOVE
                ),
            ]);

        if ($showNext === true) {
            $buttons[] =
                InlineKeyboardButtonType::create(sprintf(
                    '️%s%s',
                    EmojiCode::NEXT,
                    $this->translator->trans('next')
                ), [
                    'callbackData' => sprintf(
                        '%s?page=%s',
                        CommandCallbackEnum::HABIT_LIST,
                        $page + 1
                    ),
                ]);
        }

        return InlineKeyboardMarkupType::create([$buttons]);
    }
}
