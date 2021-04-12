<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Habit\CreationHabitState;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\HabitRemindTimeKeyboard;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;

class AddRemindTimeCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_add_remind_time';
    public const ERROR_TIME_MESSAGE = 'It doesn\'t look like the time, let\'s try again in the format - Hours: Minutes (example - 04:20)';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private HabitService $habitService;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitService $habitService
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitService = $habitService;
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
            && $draftHabit->getCreationState() === CreationHabitState::PERIOD_ADDED;
    }

    public function run(MessageType $message, User $user): void
    {
        $habit = $user->getDraftHabit();
        $remindAtString = trim($message->text);

        try {
            $remindAt = new \DateTimeImmutable($remindAtString);
        } catch (\Throwable $exception) {
            $this->handleError($message, self::ERROR_TIME_MESSAGE);

            return;
        }

        $habit->setRemindAt($remindAt);
        $this->habitService->save($habit);
    }

    private function handleError(MessageType $message, string $error): void
    {
        $this->bot->sendMessage(
            SendMessageMethod::create(
                $message->chat->id,
                $error, [
                    'replyMarkup' => HabitRemindTimeKeyboard::generate(),
                ]
            )
        );
    }
}
