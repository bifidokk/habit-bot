<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\LocaleSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriberTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        $events = LocaleSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertSame([['onKernelRequest', 20]], $events[KernelEvents::REQUEST]);
    }
}
