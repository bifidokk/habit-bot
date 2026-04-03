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
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function generate(Habit $habit): InlineKeyboardMarkupType
    {
        $habitId = $habit->getId()->toRfc4122();

        return InlineKeyboardMarkupType::create([
            [
                InlineKeyboardButtonType::create(sprintf(
                    '%s %s',
                    EmojiCode::Back->value,
                    $this->translator->trans('back')
                ), [
                    'callbackData' => sprintf(
                        '%s?id=%s',
                        CommandCallbackEnum::BackToRemindTime->value,
                        $habitId
                    ),
                ]),
            ],
            [
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
            ],
            [
                InlineKeyboardButtonType::create(sprintf(
                    '%s%s',
                    EmojiCode::Marked->value,
                    $this->translator->trans('submit')
                ), [
                    'callbackData' => sprintf(
                        '%s?id=%s',
                        CommandCallbackEnum::HabitPublish->value,
                        $habitId
                    ),
                ]),
            ],
        ]);
    }
}
