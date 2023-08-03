<?php

declare(strict_types=1);

namespace App\Service\User\Event;

use App\Entity\User;

class TimezoneChangedEvent
{
    public function __construct(private readonly User $user) {}

    public function getUser(): User
    {
        return $this->user;
    }
}
