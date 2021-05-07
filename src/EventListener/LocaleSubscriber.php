<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use TgBotApi\BotApiBase\BotApiNormalizer;
use TgBotApi\BotApiBase\Type\UserType;
use TgBotApi\BotApiBase\WebhookFetcher;

class LocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;

    public function __construct(string $defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $fetcher = new WebhookFetcher(new BotApiNormalizer());

        try {
            $update = $fetcher->fetch($request->getContent());
            $user = $update->message ? $update->message->from : $update->callbackQuery->from;

            if ($user instanceof UserType) {
                $request->setLocale($user->languageCode ?? $this->defaultLocale);
            } else {
                $request->setLocale($this->defaultLocale);
            }
        } catch (\Throwable $exception) {
            $request->setLocale($this->defaultLocale);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
