<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Keyboard\MainMenuKeyboard;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;

class MainMenuCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'main_menu';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly MainMenuKeyboard $mainMenuKeyboard,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return false;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $method = $this->createSendMethod($update->message ?? $update->callbackQuery->message);
        $this->bot->sendMessage($method);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        return SendMessageMethod::create(
            $message->chat->id,
            $this->translator->trans('command.response.main_menu'),
            [
                'replyMarkup' => $this->mainMenuKeyboard->generate(),
            ]
        );
    }
}
