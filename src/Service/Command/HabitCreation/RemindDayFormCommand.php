<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\AbstractCommand;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Habit\HabitService;
use App\Service\Habit\HabitState;
use App\Service\Keyboard\HabitRemindDayInlineKeyboard;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\EditMessageTextMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class RemindDayFormCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_remind_day_form';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly HabitService $habitService,
        private readonly HabitRemindDayInlineKeyboard $habitRemindDayInlineKeyboard,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::HabitRemindDayForm;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = $this->habitService->getHabitByIdWithState(
            $commandCallback->parameters['id'],
            HabitState::Draft
        );

        $this->bot->editMessageText(
            EditMessageTextMethod::create(
                $update->callbackQuery->message->chat->id,
                $update->callbackQuery->message->messageId,
                $this->translator->trans('command.response.habit_remind_day'),
                [
                    'replyMarkup' => $this->habitRemindDayInlineKeyboard->generate(
                        $habit->getRemindWeekDays(),
                        $habit->getId()->toRfc4122()
                    ),
                ]
            )
        );
    }
}
