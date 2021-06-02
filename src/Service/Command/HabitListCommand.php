<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\HabitViewInlineKeyboard;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\Interfaces\HasParseModeVariableInterface;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class HabitListCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_list';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private HabitViewInlineKeyboard $habitViewInlineKeyboard;
    private TranslatorInterface $translator;
    private HabitService $habitService;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitViewInlineKeyboard $habitViewInlineKeyboard,
        TranslatorInterface $translator,
        HabitService $habitService
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitViewInlineKeyboard = $habitViewInlineKeyboard;
        $this->translator = $translator;
        $this->habitService = $habitService;
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command->getValue() === CommandCallbackEnum::HABIT_LIST;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $page = (int) ($commandCallback->parameters['page'] ?? 0);
        $habits = $this->habitService->getUserHabits($user);

        if (count($habits) === 0) {
            return;
        }

        $habit = array_slice($habits, $page, 1);

        if (count($habit) === 0) {
            return;
        }

        $habit = $habit[0];
        $showNext = count(array_slice($habits, $page)) > 1; // if there are any habits after that

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $update->callbackQuery->message->chat->id,
                $this->habitService->getHabitPreviewText($habit), [
                    'parseMode' => HasParseModeVariableInterface::PARSE_MODE_MARKDOWN_V2,
                    'replyMarkup' => $this->habitViewInlineKeyboard->generate($page, $showNext),
                ])
        );
    }
}
