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
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\Interfaces\HasParseModeVariableInterface;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\UpdateType;

class PreviewCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_preview';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private HabitService $habitService;
    private HabitPreviewInlineKeyboard $habitPreviewInlineKeyboard;
    private SendMessageMethodFactory $sendMessageMethodFactory;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitService $habitService,
        HabitPreviewInlineKeyboard $habitPreviewInlineKeyboard,
        SendMessageMethodFactory $sendMessageMethodFactory
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitService = $habitService;
        $this->habitPreviewInlineKeyboard = $habitPreviewInlineKeyboard;
        $this->sendMessageMethodFactory = $sendMessageMethodFactory;
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $commandCallback !== null
            && $commandCallback->command->getValue() === CommandCallbackEnum::HABIT_PREVIEW;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $habit = $this->habitService->getHabitByIdWithState(
            $commandCallback->parameters['id'],
            HabitState::get(HabitState::DRAFT)
        );

        if ($habit->readyForPublishing()) {
            $this->bot->sendMessage(
                SendMessageMethod::create(
                    $update->callbackQuery->message->chat->id,
                    $this->habitService->getHabitPreviewText($habit), [
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
