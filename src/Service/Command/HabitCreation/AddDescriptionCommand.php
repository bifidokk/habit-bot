<?php

declare(strict_types=1);

namespace App\Service\Command\HabitCreation;

use App\Entity\User;
use App\Service\Command\AbstractCommand;
use App\Service\Command\CommandCallback;
use App\Service\Command\CommandCallbackEnum;
use App\Service\Command\CommandInterface;
use App\Service\Habit\HabitDescriptionDto;
use App\Service\Habit\HabitService;
use App\Service\Habit\HabitState;
use App\Service\InputHandler;
use App\Service\Message\SendMessageMethodFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\Method\SendMessageMethod;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;

class AddDescriptionCommand extends AbstractCommand implements CommandInterface
{
    public const COMMAND_NAME = 'habit_creation_add_title';

    public function __construct(
        private BotApiComplete $bot,
        private HabitService $habitService,
        private ValidatorInterface $validator,
        private InputHandler $inputHandler,
        private SendMessageMethodFactory $sendMessageMethodFactory,
        private TranslatorInterface $translator,
    ) {}

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
            $this->handleError($update->message, $this->translator->trans('command.error.description'));

            return;
        }

        try {
            $habit = $this->habitService->getHabitByIdWithState(
                (string) $commandCallback->parameters['id'],
                HabitState::get(HabitState::DRAFT)
            );

            $habit->setDescription($habitDescription->description);
            $this->habitService->save($habit);
        } catch (\Throwable) {
            $this->handleError($update->message, $this->translator->trans('command.error.common'));

            return;
        }

        $this->inputHandler->unwaitForInput($user);
        $this->bot->sendMessage(
            $this->sendMessageMethodFactory->createHabitMenuMethod($update->message->chat->id, $habit)
        );
    }

    private function handleError(MessageType $message, string $error): void
    {
        $this->bot->sendMessage(
            SendMessageMethod::create(
                $message->chat->id,
                sprintf($this->translator->trans('command.error.template'), $error)
            )
        );
    }
}
