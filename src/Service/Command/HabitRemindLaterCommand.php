<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Habit\HabitService;
use App\Service\Habit\RemindLaterService;
use App\Service\Keyboard\MainMenuKeyboard;
use App\Service\Message\Animation;
use App\Service\Message\AnimationType;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\EditMessageReplyMarkupMethod;
use TgBotApi\BotApiBase\Method\EditMessageTextMethod;
use TgBotApi\BotApiBase\Method\SendAnimationMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class HabitRemindLaterCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_remind_later';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly HabitService $habitService,
        private readonly TranslatorInterface $translator,
        private readonly Animation $animation,
        private readonly RemindLaterService $remindLaterService,
        private readonly MainMenuKeyboard $mainMenuKeyboard,
    ) {}

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::HabitBusy;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = $this->habitService->getHabitById($commandCallback->parameters['id']);

        if ($habit->getUser() !== $user) {
            return;
        }

        $remindInMinutes = $this->remindLaterService->remindLater($habit, new \DateTimeImmutable());

        $this->bot->editMessageReplyMarkup(
            EditMessageReplyMarkupMethod::create(
                $update->callbackQuery->message->chat->id,
                $update->callbackQuery->message->messageId,
                [
                    'replyMarkup' => $this->mainMenuKeyboard->generate(),
                ]
            )
        );

        if ($remindInMinutes > 0) {
            $this->bot->editMessageText(
                EditMessageTextMethod::create(
                    $update->callbackQuery->message->chat->id,
                    $update->callbackQuery->message->messageId,
                    sprintf($this->translator->trans('marked_busy'), $remindInMinutes)
                )
            );

            return;
        }

        $this->bot->editMessageText(
            EditMessageTextMethod::create(
                $update->callbackQuery->message->chat->id,
                $update->callbackQuery->message->messageId,
                $this->translator->trans('marked_busy_finally')
            )
        );

        $this->bot->sendAnimation(SendAnimationMethod::create(
            $update->callbackQuery->message->chat->id,
            $this->animation->getByType(AnimationType::HabitBusy)
        ));
    }
}
