<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\AbstractCommand;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Habit\HabitService;
use App\Service\Habit\HabitState;
use App\Service\Habit\RemindService;
use App\Service\Keyboard\HabitInlineKeyboard;
use App\Service\Keyboard\HabitRemindDayInlineKeyboard;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\EditMessageTextMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddRemindDayCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_add_remind_day';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly RemindService $remindDayService,
        private readonly HabitService $habitService,
        private readonly HabitRemindDayInlineKeyboard $habitRemindDayInlineKeyboard,
        private readonly TranslatorInterface $translator,
        private readonly HabitInlineKeyboard $habitInlineKeyboard,
    ) {
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::SetHabitRemindDay;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        if ($commandCallback === null) {
            return;
        }

        $habit = $this->habitService->getHabitByIdWithState(
            $commandCallback->parameters['id'],
            HabitState::Draft
        );

        $dayName = trim($commandCallback->parameters['day'] ?? null);
        $dayNumber = array_search($dayName, HabitRemindDayInlineKeyboard::WEEK_DAYS, true);

        if ($dayNumber !== false) {
            $this->remindDayService->toggleDay($habit, (int) $dayNumber);
        }

        if ($dayName === HabitRemindDayInlineKeyboard::ALL_BUTTON) {
            $this->remindDayService->markAll($habit);
        }

        if ($dayName === HabitRemindDayInlineKeyboard::NEXT_BUTTON) {
            if ($habit->getRemindWeekDays() > 0) {
                $this->bot->editMessageText(
                    EditMessageTextMethod::create(
                        $update->callbackQuery->message->chat->id,
                        $update->callbackQuery->message->messageId,
                        $this->translator->trans('command.response.habit_creation'),
                        [
                            'replyMarkup' => $this->habitInlineKeyboard->generate($habit),
                        ]
                    )
                );

                return;
            }
        }

        $this->updateKeyboard($update, $habit);
    }

    private function updateKeyboard(UpdateType $update, Habit $habit): void
    {
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
