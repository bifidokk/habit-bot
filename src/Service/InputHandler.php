<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Service\Redis\RedisClient;

class InputHandler
{
    private const WAIT_FOR_INPUT_KEY = 'wait_for_input:%s';

    public function __construct(private readonly RedisClient $redisClient) {}

    public function waitForInput(User $user, string $callback): void
    {
        $this->redisClient->set(
            sprintf(self::WAIT_FOR_INPUT_KEY, $user->getTelegramId()),
            $callback
        );
    }

    public function unwaitForInput(User $user): int
    {
        return $this->redisClient->del(
            sprintf(self::WAIT_FOR_INPUT_KEY, $user->getTelegramId())
        );
    }

    public function checkForInput(User $user): ?string
    {
        $command = $this->redisClient->get(sprintf(self::WAIT_FOR_INPUT_KEY, $user->getTelegramId()));

        return is_string($command) ? $command : null;
    }
}
