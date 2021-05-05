<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Command\CommandPriority;
use App\Service\Habit\HabitDescriptionDto;
use App\Service\Habit\HabitService;
use App\Service\Habit\HabitState;
use App\Service\InputHandler;
use App\Service\Keyboard\HabitInlineKeyboard;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddDescriptionCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_add_title';
    public const ERROR_TEMPLATE_TEXT = 'There is an error: %s';
    public const ERROR_DESCRIPTION_TEXT = 'Invalid habit description';
    public const ERROR_TEXT = 'Something went wrong';

    private BotApiComplete $bot;
    private LoggerInterface $logger;
    private HabitService $habitService;
    private ValidatorInterface $validator;
    private InputHandler $inputHandler;
    private HabitInlineKeyboard $habitInlineKeyboard;

    public function __construct(
        BotApiComplete $bot,
        LoggerInterface $logger,
        HabitService $habitService,
        ValidatorInterface $validator,
        InputHandler $inputHandler,
        HabitInlineKeyboard $habitInlineKeyboard
    ) {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->habitService = $habitService;
        $this->validator = $validator;
        $this->inputHandler = $inputHandler;
        $this->habitInlineKeyboard = $habitInlineKeyboard;
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
        return $commandCallback !== null
            && $commandCallback->command->getValue() === CommandCallbackEnum::SET_HABIT_DESCRIPTION;
    }

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void
    {
        if ($update->message === null) {
            return;
        }

        $habitDescription = HabitDescriptionDto::fromMessage($update->message);
        $errors = $this->validator->validate($habitDescription);

        if (count($errors) > 0) {
            $this->handleError($update->message, self::ERROR_DESCRIPTION_TEXT);

            return;
        }

        try {
            $habit = $this->habitService->getHabitByIdWithState(
                (string) $commandCallback->parameters['id'],
                HabitState::get(HabitState::DRAFT)
            );

            $habit->setDescription($habitDescription->description);
            $this->habitService->save($habit);
        } catch (\Throwable $e) {
            $this->handleError($update->message, self::ERROR_TEXT);

            return;
        }

        $this->inputHandler->unwaitForInput($user);

        $method = $this->createSendMethod($update->message, $habit);
        $this->bot->sendMessage($method);
    }

    private function createSendMethod(MessageType $message, Habit $habit): SendMessageMethod
    {
        return SendMessageMethod::create(
            $message->chat->id,
            StartCommand::COMMAND_RESPONSE_TEXT, [
                'replyMarkup' => $this->habitInlineKeyboard->generate($habit),
            ]);
    }

    private function handleError(MessageType $message, string $error): void
    {
        $this->bot->sendMessage(
            SendMessageMethod::create(
                $message->chat->id,
                sprintf(self::ERROR_TEMPLATE_TEXT, $error)
            )
        );
    }
}