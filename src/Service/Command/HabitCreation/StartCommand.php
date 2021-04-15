<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\NewHabitKeyboard;
use App\Service\User\UserService;
use App\Service\User\UserStateTransition;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;

class StartCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_start';
    public const COMMAND_PHRASE = 'Add a new habit';
    public const COMMAND_RESPONSE_TEXT = 'Just enter a new habit\'s text';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private UserService $userService;
    private HabitService $habitService;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        UserService $userService,
        HabitService $habitService
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->userService = $userService;
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
        return $message->text === self::COMMAND_PHRASE;
    }

    public function run(MessageType $message, User $user): void
    {
        $this->habitService->removeUserDraftHabits($user);
        $this->userService->changeUserState($user, UserStateTransition::get(UserStateTransition::NEW_HABIT));

        $method = $this->createSendMethod($message);
        $this->bot->sendMessage($method);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        return SendMessageMethod::create(
            $message->chat->id,
            self::COMMAND_RESPONSE_TEXT, [
                'replyMarkup' => NewHabitKeyboard::generate(),
            ]);
    }
}
