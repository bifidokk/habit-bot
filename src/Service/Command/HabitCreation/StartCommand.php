<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Habit\HabitService;
use App\Service\Habit\HabitState;
use App\Service\Message\SendMessageMethodFactory;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\UpdateType;

class StartCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_start';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private HabitService $habitService;
    private SendMessageMethodFactory $sendMessageMethodFactory;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitService $habitService,
        SendMessageMethodFactory $sendMessageMethodFactory
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitService = $habitService;
        $this->sendMessageMethodFactory = $sendMessageMethodFactory;
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
        return $commandCallback !== null && $commandCallback->command->getValue() === CommandCallbackEnum::HABIT_FORM;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = null;

        if ($commandCallback !== null && isset($commandCallback->parameters['id'])) {
            $habit = $this->habitService->getHabitByIdWithState(
                $commandCallback->parameters['id'],
                HabitState::get(HabitState::DRAFT)
            );
        }

        if ($habit === null) {
            $this->habitService->removeUserDraftHabits($user);
            $habit = $this->habitService->createHabit($user);
        }

        $user->addHabit($habit);

        $chatId = $update->message
            ? $update->message->chat->id
            : $update->callbackQuery->message->chat->id;

        $this->bot->sendMessage(
            $this->sendMessageMethodFactory->createHabitMenuMethod($chatId, $habit)
        );
    }
}
