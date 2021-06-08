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
use App\Service\Keyboard\HabitRemindTimeInlineKeyboard;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class RemindTimeFormCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_remind_time_form';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private HabitService $habitService;
    private HabitRemindTimeInlineKeyboard $habitRemindTimeInlineKeyboard;
    private TranslatorInterface $translator;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitService $habitService,
        HabitRemindTimeInlineKeyboard $habitRemindTimeInlineKeyboard,
        TranslatorInterface $translator
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitService = $habitService;
        $this->habitRemindTimeInlineKeyboard = $habitRemindTimeInlineKeyboard;
        $this->translator = $translator;
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command->getValue() === CommandCallbackEnum::HABIT_REMIND_TIME_FORM;
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
                $this->translator->trans('command.response.habit_remind_time'), [
                    'replyMarkup' => $this->habitRemindTimeInlineKeyboard->generate($habit->getId()->toRfc4122()),
                ])
        );
    }
}
