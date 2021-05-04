<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Entity\Habit;
use App\Service\Command\CommandCallbackEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitPreviewInlineKeyboard
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
                    '️%s%s',
                    EmojiCode::BACK,
                    $this->translator->trans('back')
                ), [
                    'callbackData' => sprintf(
                        '%s?id=%s',
                        CommandCallbackEnum::HABIT_FORM,
                        $habit->getId()->toRfc4122()
                    ),
                ]),
                InlineKeyboardButtonType::create(sprintf(
                    '%s%s',
                    EmojiCode::MARKED,
                    $this->translator->trans('submit')
                ), [
                    'callbackData' => sprintf(
                        '%s?id=%s',
                        CommandCallbackEnum::HABIT_PUBLISH,
                        $habit->getId()->toRfc4122()
                    ),
                ]),
            ],
        ]);
    }
}
