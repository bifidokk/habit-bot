<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
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

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        InputHandler $inputHandler
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->inputHandler = $inputHandler;
    }

    public function getName(): string
    {
        return self::COMMAND_NAME;
    }

    public function getPriority(): CommandPriority
    {
        return CommandPriority::get(CommandPriority::LOW);
    }

    public function canRun(UpdateType $update, User $user): bool
    {
        return $update->callbackQuery !== null
            && $update->callbackQuery->data === CommandCallback::HABIT_DESCRIPTION_FORM;
    }

    public function run(UpdateType $update, User $user): void
    {
        $this->inputHandler->waitForInput($user, CommandCallback::get(CommandCallback::SET_HABIT_DESCRIPTION));
        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                self::COMMAND_RESPONSE
            )
        );
    }
}
