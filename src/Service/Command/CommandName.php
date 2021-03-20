<?php

declare(strict_types=1);

namespace App\Service\Command;

use Elao\Enum\AutoDiscoveredValuesTrait;
use Elao\Enum\ReadableEnum;

class CommandName extends ReadableEnum
{
    use AutoDiscoveredValuesTrait;

    public const NEW_CUSTOM_HABIT = 'Add a new habit';
    public const BACK = 'Back';

    public static function getName(string $commandText): ?string
    {
        return self::readables()[$commandText] ?? null;
    }

    public static function readables(): array
    {
        return [
            self::NEW_CUSTOM_HABIT => NewCustomHabitCommand::COMMAND_NAME,
            self::BACK => BackCommand::COMMAND_NAME,
        ];
    }
}
