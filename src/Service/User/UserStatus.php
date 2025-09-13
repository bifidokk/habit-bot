<?php

declare(strict_types=1);

namespace App\Service\User;

enum UserStatus: string
{
    case Active = 'active';

    case Inactive = 'inactive';
}
