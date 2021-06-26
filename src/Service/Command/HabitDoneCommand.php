<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Habit\HabitService;
use App\Service\Message\Animation;
use App\Service\Message\AnimationType;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\EditMessageReplyMarkupMethod;
use TgBotApi\BotApiBase\Method\EditMessageTextMethod;
use TgBotApi\BotApiBase\Method\SendAnimationMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class HabitDoneCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_done';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private HabitService $habitService;
    private TranslatorInterface $translator;
    private Animation $animation;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitService $habitService,
        TranslatorInterface $translator,
        Animation $animation
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitService = $habitService;
        $this->translator = $translator;
        $this->animation = $animation;
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command->getValue() === CommandCallbackEnum::HABIT_DONE;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = $this->habitService->getHabitById($commandCallback->parameters['id']);

        if ($habit->getUser() !== $user) {
            return;
        }

        $this->bot->editMessageReplyMarkup(
            EditMessageReplyMarkupMethod::create(
                $update->callbackQuery->message->chat->id,
                $update->callbackQuery->message->messageId,
                [
                    'replyMarkup' => null,
                ]
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
            $this->animation->getByType(AnimationType::get(AnimationType::HABIT_DONE))
        ));
    }
}
