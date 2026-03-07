<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Entity\Habit;
use App\Entity\User;
use App\EventListener\UserTimezoneChangedListener;
use App\Service\Habit\HabitService;
use App\Service\User\Event\TimezoneChangedEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserTimezoneChangedListenerTest extends TestCase
{
    private UserTimezoneChangedListener $listener;

    private HabitService&MockObject $habitService;

    protected function setUp(): void
    {
        $this->habitService = $this->createMock(HabitService::class);
        $this->listener = new UserTimezoneChangedListener($this->habitService);
    }

    public function testUpdatesAllPublishedHabitsRemindTime(): void
    {
        $user = new User();

        $habit1 = new Habit();
        $habit1->setUser($user);
        $habit1->setDescription('Habit 1');
        $habit1->setRemindWeekDays(127);
        $habit1->setRemindAt(new \DateTimeImmutable('09:00'));
        $habit1->publish();

        $habit2 = new Habit();
        $habit2->setUser($user);
        $habit2->setDescription('Habit 2');
        $habit2->setRemindWeekDays(127);
        $habit2->setRemindAt(new \DateTimeImmutable('10:00'));
        $habit2->publish();

        $user->addHabit($habit1);
        $user->addHabit($habit2);

        $event = new TimezoneChangedEvent($user);

        $this->habitService
            ->expects($this->exactly(2))
            ->method('updateHabitNextRemindTime');

        $this->listener->updateUserHabitsRemindTime($event);
    }

    public function testNoHabitsDoesNothing(): void
    {
        $user = new User();
        $event = new TimezoneChangedEvent($user);

        $this->habitService
            ->expects($this->never())
            ->method('updateHabitNextRemindTime');

        $this->listener->updateUserHabitsRemindTime($event);
    }
}
