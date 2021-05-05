<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\InputHandler;
use App\Service\Router;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;

class StartCommand implements CommandInterface
{
    public const COMMAND_NAME = 'start';
    public const COMMAND_RESPONSE_TEXT = '';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private Router $router;
    private InputHandler $inputHandler;
    private TranslatorInterface $translator;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        Router $router,
        InputHandler $inputHandler,
        TranslatorInterface $translator
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->router = $router;
        $this->inputHandler = $inputHandler;
        $this->translator = $translator;
    }

    public function getName(): string
    {
        return self::COMMAND_NAME;
    }

    public function getPriority(): CommandPriority
    {
        return CommandPriority::get(CommandPriority::HIGH);
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

        $nextCommand = $this->router->getCommandByName(MainMenuCommand::COMMAND_NAME);
        $nextCommand->run($update, $user, $commandCallback);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        return SendMessageMethod::create(
            $message->chat->id,
            $this->translator->trans('command.response.start')
        );
    }
}
