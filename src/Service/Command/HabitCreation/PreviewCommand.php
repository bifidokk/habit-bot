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
use App\Service\Keyboard\HabitPreviewInlineKeyboard;
use App\Service\Message\SendMessageMethodFactory;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\EditMessageTextMethod;
use TgBotApi\BotApiBase\Method\Interfaces\HasParseModeVariableInterface;
use TgBotApi\BotApiBase\Type\UpdateType;

class PreviewCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_preview';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly HabitService $habitService,
        private readonly HabitPreviewInlineKeyboard $habitPreviewInlineKeyboard,
        private readonly SendMessageMethodFactory $sendMessageMethodFactory,
    ) {
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::HabitPreview;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = $this->habitService->getHabitByIdWithState(
            $commandCallback->parameters['id'],
            HabitState::Draft
        );

        if ($habit->readyForPublishing()) {
            $this->bot->editMessageText(
                EditMessageTextMethod::create(
                    $update->callbackQuery->message->chat->id,
                    $update->callbackQuery->message->messageId,
                    $this->habitService->getHabitPreviewText($habit),
                    [
                        'parseMode' => HasParseModeVariableInterface::PARSE_MODE_MARKDOWN_V2,
                        'replyMarkup' => $this->habitPreviewInlineKeyboard->generate($habit),
                    ]
                )
            );
        } else {
            $this->bot->sendMessage(
                $this->sendMessageMethodFactory->createHabitMenuMethod(
                    $update->callbackQuery->message->chat->id,
                    $habit
                )
            );
        }
    }
}
