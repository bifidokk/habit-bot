<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Controller\WebhookController;
use App\EventListener\WebhookRequestListener;
use App\Service\Webhook\WebhookService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class WebhookRequestListenerTest extends TestCase
{
    private WebhookRequestListener $listener;

    private WebhookService&MockObject $webhookService;

    protected function setUp(): void
    {
        $this->webhookService = $this->createMock(WebhookService::class);
        $this->listener = new WebhookRequestListener($this->webhookService);
    }

    public function testValidTokenAllowsRequest(): void
    {
        $request = new Request([], [], ['_controller' => WebhookController::class . '::webhook', 'token' => 'valid-token']);
        $event = $this->createRequestEvent($request);

        $this->webhookService
            ->expects($this->once())
            ->method('isTokenValid')
            ->with('valid-token')
            ->willReturn(true);

        $this->listener->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    public function testInvalidTokenReturns403(): void
    {
        $request = new Request([], [], ['_controller' => WebhookController::class . '::webhook', 'token' => 'bad-token']);
        $event = $this->createRequestEvent($request);

        $this->webhookService
            ->expects($this->once())
            ->method('isTokenValid')
            ->with('bad-token')
            ->willReturn(false);

        $this->listener->onKernelRequest($event);

        $this->assertNotNull($event->getResponse());
        $this->assertSame(Response::HTTP_FORBIDDEN, $event->getResponse()->getStatusCode());
    }

    public function testNonWebhookRouteSkipped(): void
    {
        $request = new Request([], [], ['_controller' => 'App\Controller\SomeOtherController::index']);
        $event = $this->createRequestEvent($request);

        $this->webhookService
            ->expects($this->never())
            ->method('isTokenValid');

        $this->listener->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    public function testSubscribedEvents(): void
    {
        $events = WebhookRequestListener::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
    }

    private function createRequestEvent(Request $request): RequestEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
    }
}
