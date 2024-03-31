<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\KeyboardButtonType;
use TgBotApi\BotApiBase\Type\ReplyKeyboardMarkupType;

class MainMenuKeyboard
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function generate(?string $language = null): ReplyKeyboardMarkupType
    {
        return ReplyKeyboardMarkupType::create([
            [
                KeyboardButtonType::create(sprintf(
                    '%s %s',
                    EmojiCode::Alarm->value,
                    $this->translator->trans('habits', [], null, $language)
                )),
                KeyboardButtonType::create(sprintf(
                    '%s %s',
                    EmojiCode::Settings->value,
                    $this->translator->trans('settings', [], null, $language)
                )),
            ],
        ], [
            'resizeKeyboard' => true,
        ]);
    }
}
