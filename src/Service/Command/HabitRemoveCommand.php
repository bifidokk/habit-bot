<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Habit\HabitService;
use App\Service\Message\Animation;
use App\Service\Message\AnimationType;
use App\Service\Router;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\EditMessageTextMethod;
use TgBotApi\BotApiBase\Method\Interfaces\HasParseModeVariableInterface;
use TgBotApi\BotApiBase\Method\SendAnimationMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class HabitRemoveCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_remove';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly HabitService $habitService,
        private readonly TranslatorInterface $translator,
        private readonly Animation $animation,
        private readonly Router $router,
    ) {
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::HabitRemove;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $isConfirmed = (bool) $commandCallback->parameters['c'];

        if (! $isConfirmed) {
            $nextCommand = $this->router->getCommandByName(HabitListCommand::COMMAND_NAME);
            $nextCommand->run($update, $user, $commandCallback);

            return;
        }

        $habit = $this->habitService->getHabitById($commandCallback->parameters['id']);

        if ($habit->getUser() !== $user) {
            return;
        }

        $this->habitService->removeHabit($habit);

        $this->bot->editMessageText(
            EditMessageTextMethod::create(
                $update->callbackQuery->message->chat->id,
                $update->callbackQuery->message->messageId,
                $this->translator->trans('command.response.removed'),
                [
                    'parseMode' => HasParseModeVariableInterface::PARSE_MODE_MARKDOWN_V2,
                ]
            )
        );

        $this->bot->sendAnimation(SendAnimationMethod::create(
            $update->callbackQuery->message->chat->id,
            $this->animation->getByType(AnimationType::Removed)
        ));
    }
}
