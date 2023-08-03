<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\HabitConfirmRemoveInlineKeyboard;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\Interfaces\HasParseModeVariableInterface;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class HabitRemoveConfirmCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_remove_confirm';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly HabitConfirmRemoveInlineKeyboard $habitConfirmRemoveInlineKeyboard,
        private readonly HabitService $habitService,
    ) {}

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command === CommandCallbackEnum::HabitRemoveConfirm;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = $this->habitService->getHabitById($commandCallback->parameters['id']);

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $this->habitService->getHabitRemoveConfirmText($habit), [
                    'parseMode' => HasParseModeVariableInterface::PARSE_MODE_MARKDOWN_V2,
                    'replyMarkup' => $this->habitConfirmRemoveInlineKeyboard->generate($habit),
                ])
        );
    }
}
