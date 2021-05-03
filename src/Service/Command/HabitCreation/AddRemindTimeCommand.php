<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Habit\HabitService;
use App\Service\Habit\HabitState;
use App\Service\Keyboard\HabitInlineKeyboard;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddRemindTimeCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_add_remind_time';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private HabitService $habitService;
    private HabitInlineKeyboard $habitInlineKeyboard;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitService $habitService,
        HabitInlineKeyboard $habitInlineKeyboard
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitService = $habitService;
        $this->habitInlineKeyboard = $habitInlineKeyboard;
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
            && $commandCallback->command->getValue() === CommandCallbackEnum::SET_HABIT_REMIND_TIME;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        if ($commandCallback === null) {
            return;
        }

        $habit = $this->habitService->getHabitByIdWithState(
            $commandCallback->parameters['id'],
            HabitState::get(HabitState::DRAFT)
        );

        $remindAtString = trim($commandCallback->parameters['time'] ?? null);

        try {
            $remindAt = new \DateTimeImmutable($remindAtString);
        } catch (\Throwable $exception) {
            return;
        }

        $habit->setRemindAt($remindAt);
        $this->habitService->save($habit);

        $method = $this->createHabitMenuSendMethod($update, $habit);
        $this->bot->sendMessage($method);
    }

    private function createHabitMenuSendMethod(UpdateType $update, Habit $habit): SendMessageMethod
    {
        return SendMessageMethod::create(
            $update->callbackQuery->message->chat->id,
            StartCommand::COMMAND_RESPONSE_TEXT, [
                'replyMarkup' => $this->habitInlineKeyboard->generate($habit),
            ]);
    }
}
