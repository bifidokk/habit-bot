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
                    EmojiCode::MARKED,
                    $this->translator->trans('done', [], null, $habit->getUser()->getLanguageCode())
                ), [
                    'callbackData' => sprintf(
                        '%s?id=%s',
                        CommandCallbackEnum::HABIT_DONE,
                        $habit->getId()
                    ),
                ]),
                InlineKeyboardButtonType::create(sprintf(
                    '️%s %s',
                    EmojiCode::BUSY,
                    $this->translator->trans('later', [], null, $habit->getUser()->getLanguageCode())
                ), [
                    'callbackData' => sprintf(
                        '%s?id=%s',
                        CommandCallbackEnum::HABIT_BUSY,
                        $habit->getId()
                    ),
                ]),
            ],
        ]);
    }
}
