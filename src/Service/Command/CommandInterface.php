<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use TgBotApi\BotApiBase\Type\MessageType;

interface CommandInterface
{
    public function run(MessageType $message, User $user): void;
}
