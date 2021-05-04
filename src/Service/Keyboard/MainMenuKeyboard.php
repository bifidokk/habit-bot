<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\KeyboardButtonType;
use TgBotApi\BotApiBase\Type\ReplyKeyboardMarkupType;

class MainMenuKeyboard
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function generate(): ReplyKeyboardMarkupType
    {
        return ReplyKeyboardMarkupType::create([
            [
                KeyboardButtonType::create(sprintf(
                    '%s %s',
                    EmojiCode::ALARM,
                    $this->translator->trans('habits')
                )),
                KeyboardButtonType::create(sprintf(
                    '%s %s',
                    EmojiCode::SETTINGS,
                    $this->translator->trans('settings')
                )),
            ],
        ], [
            'resizeKeyboard' => true,
        ]);
    }
}
