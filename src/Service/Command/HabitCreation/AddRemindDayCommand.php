<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Habit\CreationHabitState;
use App\Service\Habit\CreationHabitStateTransition;
use App\Service\Habit\HabitService;
use App\Service\Habit\RemindDayService;
use App\Service\Keyboard\HabitPeriodMenuKeyboard;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;

class AddRemindDayCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_add_remind_day';
    public const COMMAND_RESPONSE_TEXT = 'Select remind days';
    public const COMMAND_RESPONSE_NEXT_TEXT = 'You have to choose at least on day';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private HabitService $habitService;
    private ValidatorInterface $validator;
    private RemindDayService $remindDayService;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitService $habitService,
        ValidatorInterface $validator,
        RemindDayService $remindDayService
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitService = $habitService;
        $this->validator = $validator;
        $this->remindDayService = $remindDayService;
    }

    public function getName(): string
    {
        return self::COMMAND_NAME;
    }

    public function getPriority(): CommandPriority
    {
        return CommandPriority::get(CommandPriority::LOW);
    }

    public function canRun(MessageType $message, User $user): bool
    {
        $draftHabit = $user->getDraftHabit();

        return $user->inHabitCreationFlow()
            && $draftHabit !== null
            && $draftHabit->getCreationState() === CreationHabitState::TITLE_ADDED;
    }

    public function run(MessageType $message, User $user): void
    {
        $habit = $user->getDraftHabit();

        $dayName = trim($message->text);
        $dayName = str_replace(HabitPeriodMenuKeyboard::MARK_CODE, '', $dayName);
        $dayNumber = array_search($dayName, HabitPeriodMenuKeyboard::WEEK_DAYS, true);

        if ($dayNumber !== false) {
            $this->remindDayService->toggleDay($habit, (int) $dayNumber);
        }

        if ($message->text === HabitPeriodMenuKeyboard::CHOOSE_ALL_BUTTON_LABEL) {
            $this->remindDayService->markAll($habit);
        }

        if ($message->text === HabitPeriodMenuKeyboard::NEXT_BUTTON_LABEL) {
            $this->goNextStep($message, $habit);

            return;
        }

        $this->updateKeyboard($message, $habit);
    }

    private function goNextStep(MessageType $message, Habit $habit): void
    {
        if ($habit->getRemindWeekDays() > 0) {
            $this->habitService->changeHabitCreationState(
                $habit,
                CreationHabitStateTransition::get(CreationHabitStateTransition::PERIOD_ADDED)
            );

            return;
        }

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $message->chat->id,
                self::COMMAND_RESPONSE_NEXT_TEXT, [
                    'replyMarkup' => HabitPeriodMenuKeyboard::generate($habit->getRemindWeekDays()),
                ])
        );
    }

    private function updateKeyboard(MessageType $message, Habit $habit): void
    {
        $this->bot->sendMessage(
            SendMessageMethod::create(
                $message->chat->id,
                self::COMMAND_RESPONSE_TEXT, [
                    'replyMarkup' => HabitPeriodMenuKeyboard::generate($habit->getRemindWeekDays()),
                ])
        );
    }
}
