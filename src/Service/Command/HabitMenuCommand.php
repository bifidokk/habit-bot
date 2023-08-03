<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Keyboard\EmojiCode;
use App\Service\Keyboard\HabitMenuInlineKeyboard;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class HabitMenuCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_menu';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly HabitMenuInlineKeyboard $habitMenuInlineKeyboard,
        private readonly TranslatorInterface $translator,
    ) {}

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $update->message !== null
            && $update->message->text === sprintf(
                '%s %s',
                EmojiCode::Alarm->value,
                $this->translator->trans('habits')
            );
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habits = $user->getPublishedHabits();

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->message->chat->id,
                $update->message->text, [
                    'replyMarkup' => $this->habitMenuInlineKeyboard->generate($habits),
                ])
        );
    }
}
