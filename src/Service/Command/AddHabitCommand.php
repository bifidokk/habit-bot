<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\Habit\HabitService;
use App\Service\Habit\NewHabitDto;
use App\Service\Keyboard\MainMenuKeyboard;
use App\Service\Keyboard\NewHabitKeyboard;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;

class AddHabitCommand implements CommandInterface
{
    public const COMMAND_NAME = 'add_habit';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private UserService $userService;
    private HabitService $habitService;
    private ValidatorInterface $validator;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        UserService $userService,
        HabitService $habitService,
        ValidatorInterface $validator
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->userService = $userService;
        $this->habitService = $habitService;
        $this->validator = $validator;
    }

    public function getName(): string
    {
        return self::COMMAND_NAME;
    }

    public function run(MessageType $message, User $user): void
    {
        $newHabit = NewHabitDto::fromMessage($message);
        $errors = $this->validator->validate($newHabit);

        if (count($errors) > 0) {
            $this->handleError($message, 'Invalid habit description');

            return;
        }

        try {
            $this->habitService->createHabit($newHabit, $user);
        } catch (\Throwable $e) {
            $this->handleError($message, 'Something went wrong');

            return;
        }

        $this->userService->moveUserToStart($user);

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $message->chat->id,
                'New habit is added', [
                    'replyMarkup' => MainMenuKeyboard::generate(),
                ])
        );
    }

    private function handleError(MessageType $message, string $error): void
    {
        $this->bot->sendMessage(
            SendMessageMethod::create(
                $message->chat->id,
                sprintf('There is an error: %s', $error)
            )
        );

        $this->bot->sendMessage(
            SendMessageMethod::create(
                $message->chat->id,
                'Just enter a new habit\'s text', [
                    'replyMarkup' => NewHabitKeyboard::generate(),
                ]
            )
        );
    }
}
