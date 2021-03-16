<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Habit\HabitService;
use App\Service\Keyboard\MainMenuKeyboard;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;

class AddCustomHabitCommand implements CommandInterface
{
    public const COMMAND_NAME = 'add_custom_habit';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private UserService $userService;
    private HabitService $habitService;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        UserService $userService,
        HabitService $habitService
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->userService = $userService;
        $this->habitService = $habitService;
    }

    public function getName(): string
    {
        return self::COMMAND_NAME;
    }

    public function run(MessageType $message, User $user): void
    {
        $this->logger->info('AddCustomHabit');
        $habitDescription = (string) $message->text;

        try {
            $this->habitService->addHabit($habitDescription, $user);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }

        $this->userService->moveUserToStart($user);

        $method = $this->createSendMethod($message);
        $this->bot->sendMessage($method);
    }

    private function createSendMethod(MessageType $message): SendMessageMethod
    {
        return SendMessageMethod::create(
            $message->chat->id,
            'New habit is added', [
                'replyMarkup' => MainMenuKeyboard::generate(),
            ]);
    }
}
