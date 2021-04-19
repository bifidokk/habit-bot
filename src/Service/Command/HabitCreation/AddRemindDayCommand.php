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
use App\Service\Keyboard\HabitRemindDayKeyboard;
use App\Service\Router;
use Psr\Log\LoggerInterface;
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
    private RemindDayService $remindDayService;
    private Router $router;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitService $habitService,
        RemindDayService $remindDayService,
        Router $router
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitService = $habitService;
        $this->remindDayService = $remindDayService;
        $this->router = $router;
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
        $dayName = str_replace(HabitRemindDayKeyboard::MARK_CODE, '', $dayName);
        $dayNumber = array_search($dayName, HabitRemindDayKeyboard::WEEK_DAYS, true);

        if ($dayNumber !== false) {
            $this->remindDayService->toggleDay($habit, (int) $dayNumber);
        }

        if ($message->text === HabitRemindDayKeyboard::CHOOSE_ALL_BUTTON_LABEL) {
            $this->remindDayService->markAll($habit);
        }

        if ($message->text === HabitRemindDayKeyboard::NEXT_BUTTON_LABEL) {
            $this->next($message, $habit, $user);

            return;
        }

        $this->updateKeyboard($message, $habit);
    }

    private function goNextStep(MessageType $message, Habit $habit, User $user): void
    {
        if ($habit->getRemindWeekDays() > 0) {
            $this->habitService->changeHabitCreationState(
                $habit,
                CreationHabitStateTransition::get(CreationHabitStateTransition::PERIOD_ADDED)
            );

            $command = $this->router->getCommandByName(AddRemindTimeCommand::COMMAND_NAME);
            $command->run($message, $user);

            return;
        }

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $message->chat->id,
                self::COMMAND_RESPONSE_NEXT_TEXT, [
                    'replyMarkup' => HabitRemindDayKeyboard::generate($habit->getRemindWeekDays()),
                ])
        );
    }

    private function updateKeyboard(MessageType $message, Habit $habit): void
    {
        $this->bot->sendMessage(
            SendMessageMethod::create(
                $message->chat->id,
                self::COMMAND_RESPONSE_TEXT, [
                    'replyMarkup' => HabitRemindDayKeyboard::generate($habit->getRemindWeekDays()),
                ])
        );
    }
}
