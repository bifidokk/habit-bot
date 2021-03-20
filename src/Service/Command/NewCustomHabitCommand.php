<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Keyboard\NewHabitKeyboard;
use App\Service\User\UserService;
use App\Service\User\UserStateTransition;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;

class NewCustomHabitCommand implements CommandInterface
{
    public const COMMAND_NAME = 'new_custom_habit';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private UserService $userService;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        UserService $userService
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->userService = $userService;
    }

    public function getName(): string
    {
        return self::COMMAND_NAME;
    }

    public function run(MessageType $message, User $user): void
    {
        $this->userService->changeUserState($user, UserStateTransition::get(UserStateTransition::NEW_CUSTOM_HABIT));

        $method = $this->createSendMethod($message);
        $this->bot->sendMessage($method);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        return SendMessageMethod::create(
            $message->chat->id,
            'Just enter a new habit\'s text', [
                'replyMarkup' => NewHabitKeyboard::generate(),
            ]);
    }
}
