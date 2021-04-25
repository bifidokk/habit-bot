<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Habit\RemindDayService;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class PreviewCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_preview';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private RemindDayService $remindDayService;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        RemindDayService $remindDayService
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->remindDayService = $remindDayService;
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
            && $commandCallback->command->getValue() === CommandCallbackEnum::HABIT_PREVIEW;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = $user->getDraftHabit();

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $this->getHabitPreviewText($habit))
        );
    }

    private function getHabitPreviewText(Habit $habit): string
    {
        return sprintf(
            '*%s*
            every %s at %s',
            $habit->getDescription(),
            $this->remindDayService->getRemindDaysAsString($habit),
            $habit->getRemindAt() ? $habit->getRemindAt()->format('H:i') : ''
        );
    }
}
