<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\SendNewsCommand;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\News\News;
use App\Service\News\NewsProvider;
use App\Service\User\UserService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use TgBotApi\BotApiBase\BotApiComplete;

class SendNewsCommandTest extends TestCase
{
    private UserRepository&MockObject $userRepository;

    private BotApiComplete&MockObject $bot;

    private NewsProvider&MockObject $newsProvider;

    private LoggerInterface&MockObject $logger;

    private UserService&MockObject $userService;

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->bot = $this->createMock(BotApiComplete::class);
        $this->newsProvider = $this->createMock(NewsProvider::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userService = $this->createMock(UserService::class);

        $command = new SendNewsCommand(
            $this->userRepository,
            $this->bot,
            $this->newsProvider,
            $this->logger,
            $this->userService,
        );

        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithNoUsers(): void
    {
        $latestNews = new News(id: 2, ru: 'Новость', en: 'News');
        $this->newsProvider->method('getLatest')->willReturn($latestNews);
        $this->userRepository->method('findActiveUsersWithNewsIdLessThan')->willReturn([]);
        $this->bot->expects($this->never())->method('sendMessage');

        $this->commandTester->execute([]);

        $this->assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteSendsNews(): void
    {
        $user = new User();
        $reflection = new \ReflectionProperty(User::class, 'telegramId');
        $reflection->setValue($user, 12345);

        $latestNews = new News(id: 2, ru: 'Новость', en: 'News');
        $newsItem = new News(id: 2, ru: 'Новость', en: 'News');

        $this->newsProvider->method('getLatest')->willReturn($latestNews);
        $this->userRepository->method('findActiveUsersWithNewsIdLessThan')->willReturn([$user]);
        $this->newsProvider->method('getNewsAfterId')->willReturn([$newsItem]);

        $this->bot->expects($this->once())->method('sendMessage');
        $this->userRepository->expects($this->once())->method('save')->with($user);

        $this->commandTester->execute([]);

        $this->assertSame(0, $this->commandTester->getStatusCode());
        $this->assertSame(2, $user->getLastNewsId());
    }

    public function testExecuteDeactivatesUserOnForbidden(): void
    {
        $user = new User();
        $reflection = new \ReflectionProperty(User::class, 'telegramId');
        $reflection->setValue($user, 12345);

        $latestNews = new News(id: 2, ru: 'Новость', en: 'News');
        $newsItem = new News(id: 2, ru: 'Новость', en: 'News');

        $this->newsProvider->method('getLatest')->willReturn($latestNews);
        $this->userRepository->method('findActiveUsersWithNewsIdLessThan')->willReturn([$user]);
        $this->newsProvider->method('getNewsAfterId')->willReturn([$newsItem]);

        $this->bot->method('sendMessage')
            ->willThrowException(new \RuntimeException('Forbidden: bot was blocked by the user'));

        $this->logger->expects($this->once())->method('error');
        $this->userService->expects($this->once())->method('deactivateUser')->with($user);
        $this->userRepository->expects($this->never())->method('save');

        $this->commandTester->execute([]);

        $this->assertSame(0, $this->commandTester->getStatusCode());
    }
}
