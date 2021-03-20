<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\User\UserState;
use TgBotApi\BotApiBase\Type\MessageEntityType;
use TgBotApi\BotApiBase\Type\MessageType;

class CommandResolver
{
    const BACK_COMMAND = 'Back';

    public function resolve(MessageType $message, User $user): ?string
    {
        if (isset($message->entities) && $message->entities !== null) {
            return $this->parseCommandFromMessage($message);
        }

        $command = $this->resolveCommandByName($message);

        if ($command !== null) {
            return $command;
        }

        if ($this->isBackCommand($message)) {
            return $this->resolveBackCommandByUserState($user);
        }

        return $this->resolveCommandByUserState($user);
    }

    private function isBackCommand(MessageType $message): bool
    {
        return (string) $message->text === self::BACK_COMMAND;
    }

    private function parseCommandFromMessage(MessageType $message): ?string
    {
        if ($message->entities === null) {
            return null;
        }

        foreach ($message->entities as $entity) {
            if ($entity->type !== MessageEntityType::TYPE_BOT_COMMAND) {
                continue;
            }

            $command = substr($message->text, $entity->offset, $entity->length);

            return substr($command, 1);
        }

        return null;
    }

    private function resolveCommandByName(MessageType $message): ?string
    {
        return CommandName::getName((string) $message->text);
    }

    private function resolveCommandByUserState(User $user): ?string
    {
        switch ($user->getState()) {
            case UserState::NEW_CUSTOM_HABIT:
                return AddCustomHabitCommand::COMMAND_NAME;
        }

        return null;
    }

    private function resolveBackCommandByUserState(User $user): ?string
    {
        switch ($user->getState()) {
            case UserState::NEW_CUSTOM_HABIT:
                return StartCommand::COMMAND_NAME;
        }

        return null;
    }
}
