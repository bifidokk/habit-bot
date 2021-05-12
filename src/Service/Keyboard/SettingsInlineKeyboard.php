<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Service\Command\CommandCallbackEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class SettingsInlineKeyboard
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
                    EmojiCode::CLOCKS,
                    $this->translator->trans('settings_menu.timezone')
                ), [
                    'callbackData' => CommandCallbackEnum::SETTINGS_TIMEZONE_FORM,
                ]
            )],
        ]);
    }
}
