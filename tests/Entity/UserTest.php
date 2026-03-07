<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Telegram\TelegramUser;
use App\Service\User\UserStatus;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetTimezone(): void
    {
        $user = new User();
        $user->setTimezone(new \DateTimeZone('Europe/Moscow'));

        $timezone = $user->getTimezone();

        $this->assertInstanceOf(\DateTimeZone::class, $timezone);
        $this->assertSame('Europe/Moscow', $timezone->getName());
    }

    public function testGetPublishedHabits(): void
    {
        $user = new User();

        $draftHabit = new Habit();
        $draftHabit->setUser($user);
        $draftHabit->setDescription('Draft');
        $user->addHabit($draftHabit);

        $publishedHabit = new Habit();
        $publishedHabit->setUser($user);
        $publishedHabit->setDescription('Published');
        $publishedHabit->publish();
        $user->addHabit($publishedHabit);

        $publishedHabits = $user->getPublishedHabits();

        $this->assertCount(1, $publishedHabits);
        $this->assertSame($publishedHabit, array_values($publishedHabits)[0]);
    }

    public function testDeactivate(): void
    {
        $user = new User();

        $this->assertTrue($user->isActive());

        $user->deactivate();

        $this->assertFalse($user->isActive());
    }

    public function testCreateFromTelegramUser(): void
    {
        $telegramUser = new TelegramUser(
            id: '12345',
            firstName: 'John',
            lastName: 'Doe',
            username: 'johndoe',
            languageCode: 'en',
            photoUrl: 'https://example.com/photo.jpg',
        );

        $user = User::createFromTelegramUser($telegramUser);

        $this->assertSame('johndoe', $user->getUsername());
        $this->assertSame('en', $user->getLanguageCode());
        $this->assertSame(12345, $user->getTelegramId());
    }

    public function testGetRolesAlwaysIncludesRoleUser(): void
    {
        $user = new User();

        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
    }

    public function testGetUserIdentifier(): void
    {
        $user = new User();

        // ID is null for a new entity not persisted
        $this->assertSame('', $user->getUserIdentifier());
    }
}
