<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Entity\Habit;
use App\Service\Command\CommandCallbackEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitDoneKeyboard
{
    public function __construct(private readonly TranslatorInterface $translator) {}

    public function generate(Habit $habit): InlineKeyboardMarkupType
    {
        return InlineKeyboardMarkupType::create([
            [
                InlineKeyboardButtonType::create(sprintf(
                    '️%s %s',
                    EmojiCode::Marked->value,
                    $this->translator->trans('done', [], null, $habit->getUser()->getLanguageCode())
                ), [
                    'callbackData' => sprintf(
                        '%s?id=%s',
                        CommandCallbackEnum::HabitDone->value,
                        $habit->getId()
                    ),
                ]),
                InlineKeyboardButtonType::create(sprintf(
                    '️%s %s',
                    EmojiCode::Busy->value,
                    $this->translator->trans('later', [], null, $habit->getUser()->getLanguageCode())
                ), [
                    'callbackData' => sprintf(
                        '%s?id=%s',
                        CommandCallbackEnum::HabitBusy->value,
                        $habit->getId()
                    ),
                ]),
            ],
        ]);
    }
}
