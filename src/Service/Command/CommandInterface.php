<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Entity\User;
use TgBotApi\BotApiBase\Type\UpdateType;

interface CommandInterface
{
    public function getName(): string;

    public function getPriority(): CommandPriority;

    public function canRun(UpdateType $update, User $user, ?CommandCallback $commandCallback): bool;

    public function run(UpdateType $update, User $user, ?CommandCallback $commandCallback): void;
}
