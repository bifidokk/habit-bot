<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\HabitInlineKeyboard;
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

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return ($update->message !== null && $update->message->text === self::COMMAND_PHRASE)
            || ($commandCallback !== null && $commandCallback->command->getValue() === CommandCallbackEnum::HABIT_FORM);
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = null;

        if ($commandCallback !== null && isset($commandCallback->parameters['id'])) {
            $habit = $this->habitService->getHabit($commandCallback->parameters['id']);
        }

        if ($habit === null) {
            $this->habitService->removeUserDraftHabits($user);
            $habit = $this->habitService->createHabit($user);
        }

        $user->addHabit($habit);

        $method = $this->createSendMethod($update->message ? $update->message : $update->callbackQuery->message, $habit);
        $this->bot->sendMessage($method);
    }

    private function createSendMethod(MessageType $message, Habit $habit): SendMessageMethod
    {
        return SendMessageMethod::create(
            $message->chat->id,
            self::COMMAND_RESPONSE_TEXT, [
                'replyMarkup' => HabitInlineKeyboard::generate($habit),
            ]);
    }
}
