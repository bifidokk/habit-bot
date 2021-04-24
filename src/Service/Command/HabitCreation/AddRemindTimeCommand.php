<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Command\MainMenuCommand;
use App\Service\Habit\CreationHabitState;
use App\Service\Habit\CreationHabitStateTransition;
use App\Service\Habit\HabitService;
use App\Service\Habit\HabitStateTransition;
use App\Service\Keyboard\HabitRemindTimeKeyboard;
use App\Service\Router;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddRemindTimeCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_add_remind_time';
    public const COMMAND_RESPONSE_TEXT = 'Select remind time or text it in the format - Hours: Minutes (example - 04:20)';
    public const COMMAND_SUCCESS_TEXT = 'The habit was created successfully';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private HabitService $habitService;
    private Router $router;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitService $habitService,
        Router $router
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitService = $habitService;
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

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        $draftHabit = $user->getDraftHabit();

        return $user->inHabitCreationFlow()
            && $draftHabit !== null
            && $draftHabit->getCreationState() === CreationHabitState::PERIOD_ADDED;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = $user->getDraftHabit();
        $remindAtString = trim($update->message->text);

        try {
            $remindAt = new \DateTimeImmutable($remindAtString);
        } catch (\Throwable $exception) {
            $this->handleError($update->message, self::COMMAND_RESPONSE_TEXT);

            return;
        }

        $habit->setRemindAt($remindAt);
        $this->habitService->save($habit);

        $this->habitService->changeHabitCreationState(
            $habit,
            CreationHabitStateTransition::get(CreationHabitStateTransition::TIME_ADDED)
        );

        $this->habitService->changeHabitState(
            $habit,
            HabitStateTransition::get(HabitStateTransition::PUBLISH)
        );

        $this->bot->sendMessage(
            SendMessageMethod::create($message->chat->id, self::COMMAND_SUCCESS_TEXT)
        );

        $command = $this->router->getCommandByName(MainMenuCommand::COMMAND_NAME);
        $command->run($message, $user, $commandCallback);
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
