<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TgBotApi\BotApiBase\BotApiComplete;

class Command extends WebTestCase
{
    protected BotApiComplete $botApiCompleteMock;
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->botApiCompleteMock = $this->createMock(\TgBotApi\BotApiBase\BotApiComplete::class);

        $this->client = static::createClient();
        $this->client->getContainer()->set('TgBotApi\BotApiBase\BotApiComplete', $this->botApiCompleteMock);
        $this->client->disableReboot();
    }

    protected function sendRequest(string $content): void
    {
        $this->client->request('POST', sprintf('/webhook/%s', getenv('TG_BOT_WEBHOOK_TOKEN')), [], [], [], $content);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
