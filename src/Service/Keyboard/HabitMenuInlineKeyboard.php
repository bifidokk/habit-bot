<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Service\Command\CommandCallbackEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitMenuInlineKeyboard
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function generate(): InlineKeyboardMarkupType
    {
        return InlineKeyboardMarkupType::create([
            [InlineKeyboardButtonType::create(
                sprintf(
                    '%s%s',
                    EmojiCode::PLUS,
                    $this->translator->trans('habit.menu.add_new_habit')
                ), [
                    'callbackData' => CommandCallbackEnum::HABIT_FORM,
                ]
            )],
        ]);
    }
}
