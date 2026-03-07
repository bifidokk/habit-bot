<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\SendNewsPreviewCommand;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\News\News;
use App\Service\News\NewsProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use TgBotApi\BotApiBase\BotApiComplete;

class SendNewsPreviewCommandTest extends TestCase
{
    private UserRepository&MockObject $userRepository;

    private BotApiComplete&MockObject $bot;

    private NewsProvider&MockObject $newsProvider;

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->newsProvider = $this->createMock(NewsProvider::class);

        $command = new SendNewsPreviewCommand(
            $this->userRepository,
            $this->bot,
            $this->newsProvider,
        );

        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteUserNotFound(): void
    {
        $this->userRepository->method('find')->willReturn(null);
        $this->bot->expects($this->never())->method('sendMessage');

        $this->commandTester->execute([]);

        $this->assertSame(1, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Preview user not found', $this->commandTester->getDisplay());
    }

    public function testExecuteSendsPreview(): void
    {
        $user = new User();
        $reflection = new \ReflectionProperty(User::class, 'telegramId');
        $reflection->setValue($user, 12345);

        $this->userRepository->method('find')->willReturn($user);

        $latestNews = new News(id: 2, ru: 'Новость', en: 'News');
        $this->newsProvider->method('getLatest')->willReturn($latestNews);

        $this->bot->expects($this->once())->method('sendMessage');

        $this->commandTester->execute([]);

        $this->assertSame(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Preview sent successfully', $this->commandTester->getDisplay());
    }
}
