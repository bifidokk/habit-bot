<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use App\Service\User\UserState;
use TgBotApi\BotApiBase\Type\MessageEntityType;
use TgBotApi\BotApiBase\Type\MessageType;

class CommandResolver
{
    public function resolve(MessageType $message, User $user): ?string
    {
        if (isset($message->entities) && $message->entities !== null) {
            return $this->parseCommandFromMessage($message);
        }

        $command = $this->resolveCommandByName($message);

        if ($command !== null) {
            return $command;
        }

        return $this->resolveCommandByUserState($user);
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
                return AddHabitCommand::COMMAND_NAME;
        }

        return null;
    }
}
