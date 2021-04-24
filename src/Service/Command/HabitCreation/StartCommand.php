<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\HabitInlineKeyboard;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;

class StartCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_start';
    public const COMMAND_PHRASE = 'Add a new habit';
    public const COMMAND_RESPONSE_TEXT = 'Please fill all fields';

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

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $update->message !== null && $update->message->text === self::COMMAND_PHRASE;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $this->habitService->removeUserDraftHabits($user);

        $method = $this->createSendMethod($update->message);
        $this->bot->sendMessage($method);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        return SendMessageMethod::create(
            $message->chat->id,
            self::COMMAND_RESPONSE_TEXT, [
                'replyMarkup' => HabitInlineKeyboard::generate(new Habit()),
            ]);
    }
}
