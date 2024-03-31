<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Service\User\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use TgBotApi\BotApiBase\BotApiNormalizer;
use TgBotApi\BotApiBase\Type\UserType;
use TgBotApi\BotApiBase\WebhookFetcher;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserService $userService,
        private readonly string $defaultLocale = 'en',
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $fetcher = new WebhookFetcher(new BotApiNormalizer());

        try {
            $update = $fetcher->fetch($request->getContent());
            $userType = $update->message ? $update->message->from : $update->callbackQuery?->from;
            $user = $this->userService->getUser($update);

            if ($user instanceof User) {
                $request->setLocale($user->getLanguageCode() ?? $this->defaultLocale);
            } elseif ($userType instanceof UserType) {
                $request->setLocale($userType->languageCode ?? $this->defaultLocale);
            } else {
                $request->setLocale($this->defaultLocale);
            }
        } catch (\Throwable) {
            $request->setLocale($this->defaultLocale);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
