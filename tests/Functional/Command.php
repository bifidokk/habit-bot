<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TgBotApi\BotApiBase\BotApiComplete;

class Command extends WebTestCase
{
    protected BotApiComplete $botApiCompleteMock;
    protected KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->botApiCompleteMock = $this->createMock(\TgBotApi\BotApiBase\BotApiComplete::class);

        $this->client = static::createClient();
        $this->client->getContainer()->set('TgBotApi\BotApiBase\BotApiComplete', $this->botApiCompleteMock);
        $this->client->disableReboot();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $connection = $this->entityManager->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();

        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        }

        $purger = new ORMPurger($this->entityManager);
        $purger->setPurgeMode(2);
        $purger->purge();

        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    protected function sendRequest(string $content): void
    {
        $this->client->request('POST', sprintf('/webhook/%s', getenv('TG_BOT_WEBHOOK_TOKEN')), [], [], [], $content);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
