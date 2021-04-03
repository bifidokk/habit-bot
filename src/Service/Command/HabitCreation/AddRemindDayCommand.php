<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Habit\CreationHabitState;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\HabitPeriodMenuKeyboard;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;

class AddRemindDayCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_add_remind_day';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private HabitService $habitService;
    private ValidatorInterface $validator;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitService $habitService,
        ValidatorInterface $validator
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitService = $habitService;
        $this->validator = $validator;
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

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $message->chat->id,
                'Select remind days', [
                    'replyMarkup' => HabitPeriodMenuKeyboard::generate($habit->getRemindWeekDays()),
                ])
        );
    }
}
