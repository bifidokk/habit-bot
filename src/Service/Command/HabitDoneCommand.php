<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Event\Habit\HabitDoneEvent;
use App\Service\Habit\HabitService;
use App\Service\Message\Animation;
use App\Service\Message\AnimationType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\EditMessageReplyMarkupMethod;
use TgBotApi\BotApiBase\Method\EditMessageTextMethod;
use TgBotApi\BotApiBase\Method\SendAnimationMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class HabitDoneCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_done';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly HabitService $habitService,
        private readonly TranslatorInterface $translator,
        private readonly Animation $animation,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::HabitDone;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = $this->habitService->getHabitById($commandCallback->parameters['id']);

        if ($habit->getUser() !== $user) {
            return;
        }

        $this->eventDispatcher->dispatch(new HabitDoneEvent($habit));

        $this->bot->editMessageReplyMarkup(
            EditMessageReplyMarkupMethod::create(
                $update->callbackQuery->message->chat->id,
                $update->callbackQuery->message->messageId
            )
        );

        $this->bot->editMessageText(
            EditMessageTextMethod::create(
                $update->callbackQuery->message->chat->id,
                $update->callbackQuery->message->messageId,
                $this->translator->trans('marked_done')
            )
        );

        $this->bot->sendAnimation(SendAnimationMethod::create(
            $update->callbackQuery->message->chat->id,
            $this->animation->getByType(AnimationType::HabitDone)
        ));
    }
}
