<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Service\Command\CommandCallbackEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class UserLanguageInlineKeyboard
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function generate(): InlineKeyboardMarkupType
    {
        $languages = [];

        foreach ($this->getLanguageIcons() as $language => $icon) {
            $label = $this->translator->trans(sprintf('settings_menu.languages.%s', $language));
            $languages[] = [InlineKeyboardButtonType::create(
                sprintf('%s%s', $icon, $label),
                [
                    'callbackData' => sprintf('%s?lang=%s', CommandCallbackEnum::SetLanguage->value, $language),
                ]
            )];
        }

        return InlineKeyboardMarkupType::create($languages);
    }

    private function getLanguageIcons(): array
    {
        return [
            'en' => EmojiCode::English->value,
            'ru' => EmojiCode::Russian->value,
        ];
    }
}
