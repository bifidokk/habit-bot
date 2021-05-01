<?php

declare(strict_types=1);

namespace App\Service\Command;

abstract class AbstractCommand
{
    public const COMMAND_NAME = 'command';

    public function getName(): string
    {
        return self::COMMAND_NAME;
    }

    public function getPriority(): CommandPriority
    {
        return CommandPriority::get(CommandPriority::LOW);
    }
}
