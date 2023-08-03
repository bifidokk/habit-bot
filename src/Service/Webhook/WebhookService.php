<?php

declare(strict_types=1);

namespace App\Service\Webhook;

use App\Service\Router;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
use TgBotApi\BotApiBase\Type\UpdateType;
use function Sentry\captureException;

class WebhookService
{

    public function __construct(
        private readonly string $token,
        private readonly LoggerInterface $logger,
        private readonly UserService $userService,
        private readonly Router $router
    ) {}

    public function isTokenValid(string $token): bool
    {
        return $token === $this->token;
    }

    public function handleMessage(UpdateType $update): void
    {
        if ($update->message === null && $update->callbackQuery === null) {
            return;
        }

        $user = $this->userService->getUser($update);

        if ($user === null) {
            return;
        }

        try {
            $this->router->run($update, $user);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            captureException($e);
        }
    }
}
