<?php

declare(strict_types=1);

namespace App\Tests\Service\Habit;

use App\Entity\Habit;
use App\Repository\HabitRepository;
use App\Service\Habit\RemindLaterService;
use App\Service\Redis\RedisDummyClient;
use PHPUnit\Framework\TestCase;

class RemindLaterServiceTest extends TestCase
{
    private RemindLaterService $remindLaterService;

    protected function setUp(): void
    {
        parent::setUp();

        $habitRepository = $this->createMock(HabitRepository::class);
        $this->remindLaterService = new RemindLaterService($habitRepository, new RedisDummyClient());
    }

    public function testItCalculatesRemindLaterPeriodForHabitTest(): void
    {
        $habit = new Habit();
        $currentTime = new \DateTimeImmutable();

        $periodInMinutes = $this->remindLaterService->remindLater($habit, $currentTime);
        $this->assertEquals(5, $periodInMinutes);

        $periodInMinutes = $this->remindLaterService->remindLater($habit, $currentTime);
        $this->assertEquals(10, $periodInMinutes);

        $periodInMinutes = $this->remindLaterService->remindLater($habit, $currentTime);
        $this->assertEquals(15, $periodInMinutes);

        $periodInMinutes = $this->remindLaterService->remindLater($habit, $currentTime);
        $this->assertNull($periodInMinutes);
    }
}
