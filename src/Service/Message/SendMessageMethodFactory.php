<?php

declare(strict_types=1);

namespace App\Service\Message;

use App\Entity\Habit;
use App\Service\Keyboard\HabitInlineKeyboard;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\Method\SendMessageMethod;

class SendMessageMethodFactory
{
    public function __construct(
        private TranslatorInterface $translator,
        private HabitInlineKeyboard $habitInlineKeyboard,
    ) {}

    public function createHabitMenuMethod(int $chatId, Habit $habit): SendMessageMethod
    {
        return SendMessageMethod::create(
            $chatId,
            $this->translator->trans('command.response.habit_creation'), [
                'replyMarkup' => $this->habitInlineKeyboard->generate($habit),
            ]);
    }
}
