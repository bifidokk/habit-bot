<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\InputHandler;
use App\Service\Keyboard\MainMenuKeyboard;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;

class StartCommand implements CommandInterface
{
    public const COMMAND_NAME = 'start';

    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly InputHandler $inputHandler,
        private readonly TranslatorInterface $translator,
        private readonly MainMenuKeyboard $mainMenuKeyboard,
    ) {}

    public function getName(): string
    {
        return self::COMMAND_NAME;
    }

    public function getPriority(): CommandPriority
    {
        return CommandPriority::High;
    }

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool
    {
        return $update->message !== null && sprintf('/%s', $this->getName()) === $update->message->text;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        $this->inputHandler->unwaitForInput($user);

        $method = $this->createSendMethod($update->message);
        $this->bot->sendMessage($method);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        return SendMessageMethod::create(
            $message->chat->id,
            $this->translator->trans('command.response.start'), [
                'replyMarkup' => $this->mainMenuKeyboard->generate(),
            ]
        );
    }
}
