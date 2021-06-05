<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Keyboard\MainMenuKeyboard;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;

class MainMenuCommand implements CommandInterface
{
    public const COMMAND_NAME = 'main_menu';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private MainMenuKeyboard $mainMenuKeyboard;
    private TranslatorInterface $translator;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        MainMenuKeyboard $mainMenuKeyboard,
        TranslatorInterface $translator
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->mainMenuKeyboard = $mainMenuKeyboard;
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
        return false;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $method = $this->createSendMethod($update->message ? $update->message : $update->callbackQuery->message);
        $this->bot->sendMessage($method);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        return SendMessageMethod::create(
            $message->chat->id,
            $this->translator->trans('command.response.main_menu'), [
                'replyMarkup' => $this->mainMenuKeyboard->generate(),
            ]);
    }
}
