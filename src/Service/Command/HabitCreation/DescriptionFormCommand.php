<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Habit\HabitService;
use App\Service\InputHandler;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class DescriptionFormCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_description_form';
    public const COMMAND_RESPONSE = 'Enter the habit\'s description';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private InputHandler $inputHandler;
    private HabitService $habitService;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        InputHandler $inputHandler,
        HabitService $habitService
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->inputHandler = $inputHandler;
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
        return $commandCallback !== null
            && $commandCallback->command->getValue() === CommandCallbackEnum::HABIT_DESCRIPTION_FORM;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = $this->habitService->getHabit($commandCallback->parameters['id']);

        $this->inputHandler->waitForInput(
            $user,
            sprintf('%s?%s', CommandCallbackEnum::SET_HABIT_DESCRIPTION, $habit->getQueryParameter())
        );

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                self::COMMAND_RESPONSE
            )
        );
    }
}
