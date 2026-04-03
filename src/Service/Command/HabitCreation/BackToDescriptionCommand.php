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
use App\Service\InputHandler;
use App\Service\Keyboard\MainMenuKeyboard;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\DeleteMessageMethod;
use TgBotApi\BotApiBase\Method\Interfaces\HasParseModeVariableInterface;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class BackToDescriptionCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_back_to_description';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly HabitService $habitService,
        private readonly InputHandler $inputHandler,
        private readonly TranslatorInterface $translator,
        private readonly MainMenuKeyboard $mainMenuKeyboard,
    ) {
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::BackToDescription;
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

        $this->bot->deleteMessage(
            DeleteMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $update->callbackQuery->message->messageId,
            )
        );

        $this->inputHandler->waitForInput(
            $user,
            sprintf('%s?%s', CommandCallbackEnum::SetHabitDescription->value, $habit->getQueryParameter())
        );

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $this->translator->trans('command.response.habit_description'),
                [
                    'parseMode' => HasParseModeVariableInterface::PARSE_MODE_MARKDOWN_V2,
                    'replyMarkup' => $this->mainMenuKeyboard->generate($user->getLanguageCode()),
                ]
            )
        );
    }
}