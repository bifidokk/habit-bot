<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\AbstractCommand;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Habit\HabitService;
use App\Service\Habit\HabitState;
use App\Service\Message\SendMessageMethodFactory;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Type\UpdateType;

class StartCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_start';

    public function __construct(
        private BotApiComplete $bot,
        private HabitService $habitService,
        private SendMessageMethodFactory $sendMessageMethodFactory,
    ) {}

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
