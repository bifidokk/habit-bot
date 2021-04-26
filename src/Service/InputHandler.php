<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;

class InputHandler
{
    private const WAIT_FOR_INPUT_KEY = 'wait_for_input:%s';

    private \Redis $cacheClient;

    public function __construct(\Redis $cacheClient)
    {
        $this->cacheClient = $cacheClient;
    }

    public function waitForInput(User $user, string $callback): void
    {
        $this->cacheClient->set(
            sprintf(self::WAIT_FOR_INPUT_KEY, $user->getTelegramId()),
            $callback
        );
    }

    public function unwaitForInput(User $user): int
    {
        return $this->cacheClient->del(
            sprintf(self::WAIT_FOR_INPUT_KEY, $user->getTelegramId())
        );
    }

    public function checkForInput(User $user): ?string
    {
        $command = $this->cacheClient->get(sprintf(self::WAIT_FOR_INPUT_KEY, $user->getTelegramId()));

        return is_string($command) ? $command : null;
    }
}
