<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Habit\HabitService;
use App\Service\Habit\NewHabitDto;
use App\Service\Keyboard\NewHabitKeyboard;
use App\Service\Router;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;

class AddTitleCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_add_title';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private HabitService $habitService;
    private ValidatorInterface $validator;
    private Router $router;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitService $habitService,
        ValidatorInterface $validator,
        Router $router
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitService = $habitService;
        $this->validator = $validator;
        $this->router = $router;
    }

    public function getName(): string
    {
        return self::COMMAND_NAME;
    }

    public function getPriority(): CommandPriority
    {
        return CommandPriority::get(CommandPriority::LOW);
    }

    public function canRun(MessageType $message, User $user): bool
    {
        $draftHabit = $user->getDraftHabit();

        return $user->inHabitCreationFlow() && $draftHabit === null;
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
            $habit = $this->habitService->createHabit($newHabit, $user);
            $user->addHabit($habit);
        } catch (\Throwable $e) {
            $this->handleError($message, 'Something went wrong');

            return;
        }

        $command = $this->router->getCommandByName(AddRemindDayCommand::COMMAND_NAME);
        $command->run($message, $user);
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
