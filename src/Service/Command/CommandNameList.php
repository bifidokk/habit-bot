<?php

declare(strict_types=1);

namespace App\Service\Command;

class CommandNameList
{
    private static array $COMMAND_NAMES = [
        'new_custom_habit' => 'Add a new habit',
    ];

    public static function getName(string $commandText): ?string
    {
        $name = array_search($commandText, self::$COMMAND_NAMES, true);

        return is_string($name) ? $name : null;
    }
}
