<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Command\HabitCreation\AddTitleCommand as HabitCreationAddTitleCommand;
use App\Service\Command\HabitCreation\StartCommand as HabitCreationStartCommand;
use App\Service\Habit\HabitService;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TgBotApi\BotApiBase\Type\MessageType;

class NewHabitCommand implements CommandInterface
{
    public const COMMAND_NAME = 'new_habit';

    private ServiceLocator $commandLocator;
    private HabitService $habitService;

    public function __construct(
        ServiceLocator $commandLocator,
        HabitService $habitService
    ) {
        $this->commandLocator = $commandLocator;
        $this->habitService = $habitService;
    }

    public function getName(): string
    {
        return self::COMMAND_NAME;
    }

    public function canRun(MessageType $message, User $user): bool
    {
        return false;
    }

    public function run(MessageType $message, User $user): void
    {
        if (!$user->inHabitCreationFlow()) {
            $command = $this->commandLocator->get(HabitCreationStartCommand::COMMAND_NAME);
            $command->run($message, $user);

            return;
        }

        $habit = $this->habitService->getLastDraftHabitForUser($user);

        if ($habit === null) {
            $command = $this->commandLocator->get(HabitCreationAddTitleCommand::COMMAND_NAME);
            $command->run($message, $user);

            return;
        }
    }
}
