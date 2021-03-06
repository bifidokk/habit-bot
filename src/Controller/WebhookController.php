<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Webhook\WebhookService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\BotApiNormalizer;
use TgBotApi\BotApiBase\WebhookFetcher;

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
        $fetcher = new WebhookFetcher(new BotApiNormalizer());

        try {
            $update = $fetcher->fetch($request->getContent());
            $this->webhookService->handleMessage($update);
        } catch (\Throwable $e) {
            // do nothing
        }

        return new JsonResponse();
    }
}
