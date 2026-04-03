<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\AbstractCommand;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\EmojiCode;
use App\Service\Keyboard\HabitMenuInlineKeyboard;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\DeleteMessageMethod;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class CancelHabitCreationCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_cancel';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly HabitService $habitService,
        private readonly HabitMenuInlineKeyboard $habitMenuInlineKeyboard,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::CancelHabitCreation;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        if ($commandCallback === null) {
            return;
        }

        $this->habitService->removeUserDraftHabits($user);

        $habits = $user->getPublishedHabits();

        $this->bot->deleteMessage(
            DeleteMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $update->callbackQuery->message->messageId,
            )
        );

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                sprintf('%s %s', EmojiCode::Alarm->value, $this->translator->trans('habits')),
                [
                    'replyMarkup' => $this->habitMenuInlineKeyboard->generate($habits),
                ]
            )
        );
    }
}