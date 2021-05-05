<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Habit\HabitService;
use App\Service\Habit\HabitState;
use App\Service\Keyboard\HabitRemindDayInlineKeyboard;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class RemindDayFormCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_remind_day_form';
    public const COMMAND_RESPONSE = 'Choose remind day';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private HabitService $habitService;
    private HabitRemindDayInlineKeyboard $habitRemindDayInlineKeyboard;
    private TranslatorInterface $translator;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitService $habitService,
        HabitRemindDayInlineKeyboard $habitRemindDayInlineKeyboard,
        TranslatorInterface $translator
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitService = $habitService;
        $this->habitRemindDayInlineKeyboard = $habitRemindDayInlineKeyboard;
        $this->translator = $translator;
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
            && $commandCallback->command->getValue() === CommandCallbackEnum::HABIT_REMIND_DAY_FORM;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = $this->habitService->getHabitByIdWithState(
            $commandCallback->parameters['id'],
            HabitState::get(HabitState::DRAFT)
        );

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $this->translator->trans('command.response.habit_remind_day'),
                [
                    'replyMarkup' => $this->habitRemindDayInlineKeyboard->generate(
                        $habit->getRemindWeekDays(),
                        $habit->getId()->toRfc4122()
                    ),
                ]
            )
        );
    }
}
