<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Service\Command\CommandCallbackEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class UserLanguageInlineKeyboard
{
    private const LANGUAGES = [
        'en' => EmojiCode::ENGLISH,
        'ru' => EmojiCode::RUSSIAN,
    ];

    public function __construct(private TranslatorInterface $translator) {}

    public function generate(): InlineKeyboardMarkupType
    {
        $languages = [];

        foreach (self::LANGUAGES as $language => $icon) {
            $label = $this->translator->trans(sprintf('settings_menu.languages.%s', $language));
            $languages[] = [InlineKeyboardButtonType::create(
                sprintf('%s%s', $icon, $label),
                [
                    'callbackData' => sprintf('%s?lang=%s', CommandCallbackEnum::SET_LANGUAGE, $language),
                ]
            )];
        }

        return InlineKeyboardMarkupType::create($languages);
    }
}
