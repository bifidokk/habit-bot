<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use App\Entity\User;
use App\Service\Command\CommandCallbackEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class SettingsInlineKeyboard
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function generate(User $user): InlineKeyboardMarkupType
    {
        $statusEmoji = $user->isShowAnimations()
            ? EmojiCode::Marked->value
            : EmojiCode::Unmarked->value;
        $animationsLabel = sprintf('%s %s', $statusEmoji, $this->translator->trans('settings_menu.animations'));

        return InlineKeyboardMarkupType::create([
            [InlineKeyboardButtonType::create(
                sprintf(
                    '%s%s',
                    EmojiCode::Clocks->value,
                    $this->translator->trans('settings_menu.timezone')
                ),
                [
                    'callbackData' => CommandCallbackEnum::SettingsTimezoneForm->value,
                ]
            )],
            [InlineKeyboardButtonType::create(
                sprintf(
                    '%s%s',
                    EmojiCode::World->value,
                    $this->translator->trans('settings_menu.language')
                ),
                [
                    'callbackData' => CommandCallbackEnum::SettingsLanguageForm->value,
                ]
            )],
            [InlineKeyboardButtonType::create(
                sprintf(
                    '%s%s',
                    EmojiCode::Film->value,
                    $animationsLabel
                ),
                [
                    'callbackData' => CommandCallbackEnum::ToggleAnimations->value,
                ]
            )],
        ]);
    }
}
