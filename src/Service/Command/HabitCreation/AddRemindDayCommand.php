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
use App\Service\Keyboard\HabitRemindDayInlineKeyboard;
use App\Service\Message\SendMessageMethodFactory;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddRemindDayCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_add_remind_day';

    public function __construct(
        private BotApiComplete $bot,
        private RemindService $remindDayService,
        private HabitService $habitService,
        private HabitRemindDayInlineKeyboard $habitRemindDayInlineKeyboard,
        private SendMessageMethodFactory $sendMessageMethodFactory,
        private TranslatorInterface $translator,
    ) {}

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command->getValue() === CommandCallbackEnum::SET_HABIT_REMIND_DAY;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        if ($commandCallback === null) {
            return;
        }

        $habit = $this->habitService->getHabitByIdWithState(
            $commandCallback->parameters['id'],
            HabitState::get(HabitState::DRAFT)
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
                $this->bot->sendMessage(
                    $this->sendMessageMethodFactory->createHabitMenuMethod(
                        $update->callbackQuery->message->chat->id,
                        $habit
                    )
                );

                return;
            }
        }

        $this->updateKeyboard($update, $habit);
    }

    private function updateKeyboard(UpdateType $update, Habit $habit): void
    {
        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $this->translator->trans('command.response.habit_remind_day'), [
                    'replyMarkup' => $this->habitRemindDayInlineKeyboard->generate(
                        $habit->getRemindWeekDays(),
                        $habit->getId()->toRfc4122()
                    ),
                ])
        );
    }
}
