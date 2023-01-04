<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\AbstractCommand;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Command\MainMenuCommand;
use App\Service\Habit\HabitService;
use App\Service\Habit\HabitState;
use App\Service\Message\Animation;
use App\Service\Message\AnimationType;
use App\Service\Router;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendAnimationMethod;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class PublishCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_publish';

    public function __construct(
        private BotApiComplete $bot,
        private HabitService $habitService,
        private Router $router,
        private Animation $animation,
        private TranslatorInterface $translator,
    ) {}

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::HabitPublish;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = $this->habitService->getHabitByIdWithState(
            $commandCallback->parameters['id'],
            HabitState::Draft
        );

        if (!$habit->readyForPublishing()) {
            return;
        }

        $this->habitService->publish($habit);

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $this->translator->trans('command.response.habit_published'),
            )
        );

        $this->bot->sendAnimation(SendAnimationMethod::create(
            $update->callbackQuery->message->chat->id,
            $this->animation->getByType(AnimationType::Success),
        ));

        $nextCommand = $this->router->getCommandByName(MainMenuCommand::COMMAND_NAME);
        $nextCommand->run($update, $user, $commandCallback);
    }
}
