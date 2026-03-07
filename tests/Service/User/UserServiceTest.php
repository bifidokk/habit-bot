<?php

declare(strict_types=1);

namespace App\Tests\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\News\News;
use App\Service\News\NewsProvider;
use App\Service\Telegram\TelegramUser;
use App\Service\User\UserService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TgBotApi\BotApiBase\Type\MessageType;
use TgBotApi\BotApiBase\Type\UpdateType;
use TgBotApi\BotApiBase\Type\UserType;

class UserServiceTest extends TestCase
{
    private UserService $userService;

    private UserRepository&MockObject $userRepository;

    private NewsProvider&MockObject $newsProvider;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->newsProvider = $this->createMock(NewsProvider::class);

        $this->userService = new UserService(
            $this->userRepository,
            $this->newsProvider,
        );
    }

    public function testGetUserReturnsExistingUser(): void
    {
        $existingUser = new User();

        $userType = new UserType();
        $userType->id = 12345;
        $userType->firstName = 'John';

        $message = new MessageType();
        $message->from = $userType;

        $update = new UpdateType();
        $update->message = $message;

        $this->userRepository
            ->expects($this->once())
            ->method('findOneByTelegramId')
            ->with(12345)
            ->willReturn($existingUser);

        $result = $this->userService->getUser($update);

        $this->assertSame($existingUser, $result);
    }

    public function testGetUserCreatesNewUser(): void
    {
        $userType = new UserType();
        $userType->id = 99999;
        $userType->firstName = 'Jane';
        $userType->lastName = 'Doe';
        $userType->username = 'janedoe';
        $userType->languageCode = 'en';

        $message = new MessageType();
        $message->from = $userType;

        $update = new UpdateType();
        $update->message = $message;

        $this->userRepository
            ->expects($this->once())
            ->method('findOneByTelegramId')
            ->with(99999)
            ->willReturn(null);

        $latestNews = new News(id: 2, ru: 'ru', en: 'en');
        $this->newsProvider
            ->expects($this->once())
            ->method('getLatest')
            ->willReturn($latestNews);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $result = $this->userService->getUser($update);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame(99999, $result->getTelegramId());
    }

    public function testCreateFromTelegramUser(): void
    {
        $telegramUser = new TelegramUser(
            id: '123',
            firstName: 'John',
            lastName: 'Doe',
            username: 'johndoe',
            languageCode: 'en',
            photoUrl: 'https://example.com/photo.jpg',
        );

        $latestNews = new News(id: 2, ru: 'ru', en: 'en');
        $this->newsProvider
            ->expects($this->once())
            ->method('getLatest')
            ->willReturn($latestNews);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $result = $this->userService->createFromTelegramUser($telegramUser);

        $this->assertSame('johndoe', $result->getUsername());
        $this->assertSame('en', $result->getLanguageCode());
    }

    public function testCreateUserSetsLatestNewsId(): void
    {
        $telegramUser = new TelegramUser(
            id: '456',
            firstName: 'Test',
            lastName: '',
            username: 'test',
            languageCode: 'ru',
            photoUrl: '',
        );

        $latestNews = new News(id: 5, ru: 'ru', en: 'en');
        $this->newsProvider
            ->method('getLatest')
            ->willReturn($latestNews);

        $result = $this->userService->createFromTelegramUser($telegramUser);

        $this->assertSame(5, $result->getLastNewsId());
    }

    public function testDeactivateUser(): void
    {
        $user = new User();

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);

        $this->userService->deactivateUser($user);

        $this->assertFalse($user->isActive());
    }

    public function testDeactivateNullUserDoesNothing(): void
    {
        $this->userRepository
            ->expects($this->never())
            ->method('save');

        $this->userService->deactivateUser(null);
    }
}
