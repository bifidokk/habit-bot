<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Command\MainMenuCommand;
use App\Service\Habit\HabitService;
use App\Service\Habit\HabitState;
use App\Service\Message\Animation;
use App\Service\Message\AnimationType;
use App\Service\Router;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendAnimationMethod;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class PublishCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_publish';
    public const COMMAND_RESPONSE_TEXT = 'The habit is added successfully';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private HabitService $habitService;
    private Router $router;
    private Animation $animation;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitService $habitService,
        Router $router,
        Animation $animation
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitService = $habitService;
        $this->router = $router;
        $this->animation = $animation;
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
            && $commandCallback->command->getValue() === CommandCallbackEnum::HABIT_PUBLISH;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = $this->habitService->getHabitByIdWithState(
            $commandCallback->parameters['id'],
            HabitState::get(HabitState::DRAFT)
        );

        if (!$habit->readyForPublishing()) {
            return;
        }

        $this->habitService->publish($habit);

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                self::COMMAND_RESPONSE_TEXT
            )
        );

        $this->bot->sendAnimation(SendAnimationMethod::create(
            $update->callbackQuery->message->chat->id,
            $this->animation->getByType(AnimationType::get(AnimationType::SUCCESS)),
        ));

        $nextCommand = $this->router->getCommandByName(MainMenuCommand::COMMAND_NAME);
        $nextCommand->run($update, $user, $commandCallback);
    }
}