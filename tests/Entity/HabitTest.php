<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Habit;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class HabitTest extends TestCase
{
    public function testReadyForPublishing(): void
    {
        $habit = new Habit();
        $habit->setDescription('Test');
        $habit->setRemindWeekDays(127);
        $habit->setRemindAt(new \DateTimeImmutable('09:00'));

        $this->assertTrue($habit->readyForPublishing());
    }

    public function testNotReadyForPublishingMissingDescription(): void
    {
        $habit = new Habit();
        $habit->setRemindWeekDays(127);
        $habit->setRemindAt(new \DateTimeImmutable('09:00'));

        $this->assertFalse($habit->readyForPublishing());
    }

    public function testNotReadyForPublishingMissingDays(): void
    {
        $habit = new Habit();
        $habit->setDescription('Test');
        $habit->setRemindAt(new \DateTimeImmutable('09:00'));

        $this->assertFalse($habit->readyForPublishing());
    }

    public function testNotReadyForPublishingMissingRemindAt(): void
    {
        $habit = new Habit();
        $habit->setDescription('Test');
        $habit->setRemindWeekDays(127);

        $this->assertFalse($habit->readyForPublishing());
    }

    public function testPublish(): void
    {
        $habit = new Habit();

        $this->assertTrue($habit->isDraft());
        $this->assertFalse($habit->isPublished());

        $habit->publish();

        $this->assertFalse($habit->isDraft());
        $this->assertTrue($habit->isPublished());
    }

    public function testGetRemindWeekDaysArrayAllDays(): void
    {
        $habit = new Habit();
        $habit->setRemindWeekDays(127); // 1111111

        $this->assertSame([0, 1, 2, 3, 4, 5, 6], $habit->getRemindWeekDaysArray());
    }

    public function testGetRemindWeekDaysArrayMondayOnly(): void
    {
        $habit = new Habit();
        $habit->setRemindWeekDays(64); // 1000000

        $this->assertSame([0], $habit->getRemindWeekDaysArray());
    }

    public function testGetRemindWeekDaysArrayWeekdays(): void
    {
        $habit = new Habit();
        $habit->setRemindWeekDays(124); // 1111100

        $this->assertSame([0, 1, 2, 3, 4], $habit->getRemindWeekDaysArray());
    }

    public function testIsDraft(): void
    {
        $habit = new Habit();

        $this->assertTrue($habit->isDraft());
    }

    public function testIsPublished(): void
    {
        $habit = new Habit();
        $habit->publish();

        $this->assertTrue($habit->isPublished());
    }

    public function testSetAndGetUser(): void
    {
        $user = new User();
        $habit = new Habit();
        $habit->setUser($user);

        $this->assertSame($user, $habit->getUser());
    }

    public function testSetAndGetDescription(): void
    {
        $habit = new Habit();
        $habit->setDescription('Exercise daily');

        $this->assertSame('Exercise daily', $habit->getDescription());
    }
}
