<?php

declare(strict_types=1);

namespace App\Service\Keyboard;

use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Type\InlineKeyboardMarkupType;

class HabitViewInlineKeyboard
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function generate(): InlineKeyboardMarkupType
    {
        $buttons = [];
        return InlineKeyboardMarkupType::create($buttons);
    }
}
