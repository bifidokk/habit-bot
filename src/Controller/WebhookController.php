<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TgBotApi\BotApiBase\BotApiComplete;

class WebhookController
{
    private BotApiComplete $bot;

    public function __construct(BotApiComplete $bot)
    {
        $this->bot = $bot;
    }

    /**
     * @Route("/webhook/{token}")
     */
    public function webhook(Request $request): JsonResponse
    {
        return new JsonResponse();
    }
}
