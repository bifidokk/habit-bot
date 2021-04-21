<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Service\Command\CommandCallback;

class InputHandler
{
    private const WAIT_FOR_INPUT_KEY = 'wait_for_input:%s';

    private \Redis $cacheClient;

    public function __construct(\Redis $cacheClient)
    {
        $this->cacheClient = $cacheClient;
    }

    public function waitForInput(User $user, CommandCallback $callback): void
    {
        $this->cacheClient->set(
            sprintf(self::WAIT_FOR_INPUT_KEY, $user->getTelegramId()),
            $callback->getValue()
        );
    }

    public function checkForInput(User $user): CommandCallback
    {
        $command = $this->cacheClient->get(sprintf(self::WAIT_FOR_INPUT_KEY, $user->getTelegramId()));

        return CommandCallback::get($command);
    }
}
