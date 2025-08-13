<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Webhook\WebhookService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use TgBotApi\BotApiBase\BotApiComplete;
use TgBotApi\BotApiBase\BotApiNormalizer;
use TgBotApi\BotApiBase\Method\SetWebhookMethod;
use TgBotApi\BotApiBase\WebhookFetcher;
use function Sentry\captureException;

class WebhookController
{
    public function __construct(
        private readonly BotApiComplete $bot,
        private readonly WebhookService $webhookService,
        private readonly LoggerInterface $logger,
        private readonly string $baseUrl,
        private readonly string $token
    ) {
    }

    #[Route('/webhook/{token}')]
    public function webhook(Request $request): JsonResponse
    {
        $fetcher = new WebhookFetcher(new BotApiNormalizer());

        try {
            $update = $fetcher->fetch($request->getContent());
            $this->webhookService->handleMessage($update);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            captureException($e);
        }

        return new JsonResponse();
    }

    #[Route('/add-webhook')]
    public function addWebhook(): JsonResponse
    {
        $url = sprintf(
            '%s/webhook/%s',
            $this->baseUrl,
            $this->token
        );

        $this->bot->setWebhook(SetWebhookMethod::create($url));

        return new JsonResponse();
    }
}
