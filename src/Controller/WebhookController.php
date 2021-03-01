<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Webhook\WebhookMessage;
use App\Service\Webhook\WebhookService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TgBotApi\BotApiBase\BotApiComplete;

class WebhookController
{
    private BotApiComplete $bot;
    private WebhookService $webhookService;

    public function __construct(
        BotApiComplete $bot,
        WebhookService $webhookService
    ) {
        $this->bot = $bot;
        $this->webhookService = $webhookService;
    }

    /**
     * @Route("/webhook/{token}")
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $webhookMessage = WebhookMessage::fromRequestData(
                json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR)
            );
            $this->webhookService->handleMessage($webhookMessage);
        } catch (\Throwable $e) {
            // do nothing
        }

        return new JsonResponse();
    }
}
