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
use App\Service\Keyboard\HabitInlineKeyboard;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\EditMessageTextMethod;
use TgBotApi\BotApiBase\Method\Interfaces\HasParseModeVariableInterface;
use TgBotApi\BotApiBase\Type\UpdateType;

class StartCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_start';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly HabitService $habitService,
        private readonly TranslatorInterface $translator,
        private readonly HabitInlineKeyboard $habitInlineKeyboard,
    ) {
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null && $commandCallback->command === CommandCallbackEnum::HabitForm;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = null;

        if ($commandCallback !== null && isset($commandCallback->parameters['id'])) {
            $habit = $this->habitService->getHabitByIdWithState(
                $commandCallback->parameters['id'],
                HabitState::Draft
            );
        }

        if ($habit === null) {
            $this->habitService->removeUserDraftHabits($user);
            $habit = $this->habitService->createDraftHabit($user);
        }

        $user->addHabit($habit);

        $this->bot->editMessageText(
            EditMessageTextMethod::create(
                $update->callbackQuery->message->chat->id,
                $update->callbackQuery->message->messageId,
                $this->translator->trans('command.response.habit_creation'),
                [
                    'parseMode' => HasParseModeVariableInterface::PARSE_MODE_MARKDOWN_V2,
                    'replyMarkup' => $this->habitInlineKeyboard->generate($habit),
                ]
            )
        );
    }
}
