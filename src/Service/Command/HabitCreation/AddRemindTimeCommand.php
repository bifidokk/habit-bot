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

class AddRemindTimeCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_add_remind_time';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly HabitService $habitService,
        private readonly SendMessageMethodFactory $sendMessageMethodFactory,
    ) {}

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::SetHabitRemindTime;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        if ($commandCallback === null) {
            return;
        }

        $habit = $this->habitService->getHabitByIdWithState(
            $commandCallback->parameters['id'],
            HabitState::Draft
        );

        $remindAtString = trim($commandCallback->parameters['time'] ?? null);

        try {
            $remindAt = new \DateTimeImmutable($remindAtString);
        } catch (\Throwable) {
            return;
        }

        $habit->setRemindAt($remindAt);
        $this->habitService->save($habit);

        $this->bot->sendMessage(
            $this->sendMessageMethodFactory->createHabitMenuMethod(
                $update->callbackQuery->message->chat->id,
                $habit
            )
        );
    }
}
