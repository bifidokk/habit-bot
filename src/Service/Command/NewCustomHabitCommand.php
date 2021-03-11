<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\User\Exception\CouldNotFindUser;
use App\Service\User\Exception\CouldNotGetUserFromMessage;
use App\Service\User\UserService;
use App\Service\User\UserStateTransition;
use Psr\Log\LoggerInterface;

use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\ReplyKeyboardRemoveType;

class NewCustomHabitCommand implements CommandInterface
{
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

    public function run(MessageType $message): void
    {
        $user = $this->getUser($message);
        $this->userService->changeUserState($user, UserStateTransition::get(UserStateTransition::NEW_CUSTOM_HABIT));

        $method = $this->createSendMethod($message);
        $this->bot->sendMessage($method);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        $replyKeyboardRemove = new ReplyKeyboardRemoveType();
        $replyKeyboardRemove->removeKeyboard = true;

        return SendMessageMethod::create(
            $message->chat->id,
            'Just enter a new habit\'s text', [
                'replyMarkup' => $replyKeyboardRemove,
            ]);
    }

    private function getUser(MessageType $message): User
    {
        $from = $message->from;

        if ($from === null) {
            throw new CouldNotGetUserFromMessage();
        }

        $user = $this->userService->findUserByTelegramId($from->id);

        if ($user === null) {
            throw new CouldNotFindUser();
        }

        return $user;
    }
}
