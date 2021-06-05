<?php

declare(strict_types=1);

namespace App\Service\User\Event;

use App\Entity\User;

class TimezoneChangedEvent
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
