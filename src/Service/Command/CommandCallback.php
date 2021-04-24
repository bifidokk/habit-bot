<?php

declare(strict_types=1);

namespace App\Service\Command;

class CommandCallback
{
    public CommandCallbackEnum $command;

    public array $parameters = [];
}
