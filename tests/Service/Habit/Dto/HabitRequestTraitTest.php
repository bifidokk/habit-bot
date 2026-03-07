<?php

declare(strict_types=1);

namespace App\Tests\Service\Habit\Dto;

use App\Service\Habit\Dto\CreateHabitRequest;
use PHPUnit\Framework\TestCase;

class HabitRequestTraitTest extends TestCase
{
    public function testGenerateAllDays(): void
    {
        $request = new CreateHabitRequest(
            name: 'Test',
            days: [0, 1, 2, 3, 4, 5, 6],
            time: '09:00',
        );

        $this->assertSame(127, $request->generateRemindWeekDaysInteger());
    }

    public function testGenerateMondayOnly(): void
    {
        $request = new CreateHabitRequest(
            name: 'Test',
            days: [0],
            time: '09:00',
        );

        $this->assertSame(64, $request->generateRemindWeekDaysInteger());
    }

    public function testGenerateEmpty(): void
    {
        $request = new CreateHabitRequest(
            name: 'Test',
            days: [],
            time: '09:00',
        );

        $this->assertSame(0, $request->generateRemindWeekDaysInteger());
    }
}
