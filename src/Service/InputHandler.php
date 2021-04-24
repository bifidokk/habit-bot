<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Service\Command\CommandCallbackEnum;

class InputHandler
{
    private const WAIT_FOR_INPUT_KEY = 'wait_for_input:%s';

    private \Redis $cacheClient;

    public function __construct(\Redis $cacheClient)
    {
        $this->cacheClient = $cacheClient;
    }

    public function waitForInput(User $user, CommandCallbackEnum $callback): void
    {
        $this->cacheClient->set(
            sprintf(self::WAIT_FOR_INPUT_KEY, $user->getTelegramId()),
            $callback->getValue()
        );
    }

    public function unwaitForInput(User $user): int
    {
        return $this->cacheClient->del(
            sprintf(self::WAIT_FOR_INPUT_KEY, $user->getTelegramId())
        );
    }

    public function checkForInput(User $user): ?CommandCallbackEnum
    {
        $command = $this->cacheClient->get(sprintf(self::WAIT_FOR_INPUT_KEY, $user->getTelegramId()));

        if (!is_string($command)) {
            return null;
        }

        return CommandCallbackEnum::get($command);
    }
}
