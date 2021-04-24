<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Webhook\WebhookService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\BotApiNormalizer;
use TgBotApi\BotApiBase\Method\SetWebhookMethod;
use TgBotApi\BotApiBase\WebhookFetcher;

class WebhookController
{
    private BotApiComplete $bot;
    private WebhookService $webhookService;
    private LoggerInterface $logger;

    public function __construct(
        BotApiComplete $bot,
        WebhookService $webhookService,
        LoggerInterface $logger
    ) {
        $this->bot = $bot;
        $this->webhookService = $webhookService;
        $this->logger = $logger;
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
            $this->logger->error($e->getMessage());
        }

        return new JsonResponse();
    }

    /**
     * @Route("/add-webhook")
     */
    public function addWebhook(): JsonResponse
    {
        $this->bot->setWebhook(SetWebhookMethod::create('https://5016605a2570.ngrok.io/webhook/jc95oyvlRZtieaPLQRodolDTeXXs1Ek3'));
    }
}
