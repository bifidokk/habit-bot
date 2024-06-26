<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Controller\WebhookController;
use App\Service\Webhook\WebhookService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class WebhookRequestListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly WebhookService $webhookService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $controllerAttribute = $request->attributes->get('_controller');

        if ($controllerAttribute === null) {
            return;
        }

        if ($controllerAttribute !== WebhookController::class.'::webhook') {
            return;
        }

        $token = $request->get('token');

        if (! $this->webhookService->isTokenValid($token)) {
            $event->setResponse($this->getErrorResponse());
        }
    }

    private function getErrorResponse(): JsonResponse
    {
        return new JsonResponse([
            'error' => 'Invalid token',
        ], Response::HTTP_FORBIDDEN);
    }
}
