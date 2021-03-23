<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use TgBotApi\BotApiBase\Type\MessageType;

abstract class AbstractTelegramCommand
{
    public function canRun(MessageType $message, User $user): bool
    {
        return sprintf('/%s', $this->getName()) === $message->text;
    }

    abstract public function getName(): string;
}
