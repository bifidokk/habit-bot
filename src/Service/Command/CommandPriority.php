<?php

declare(strict_types=1);

namespace App\Service\Command;

enum CommandPriority: int
{
    case High = 1;
    case Medium = 0;
    case Low = -1;
}
