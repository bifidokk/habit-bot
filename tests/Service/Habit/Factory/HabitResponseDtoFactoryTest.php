<?php

declare(strict_types=1);

namespace App\Tests\Service\Habit\Factory;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Habit\Factory\HabitResponseDtoFactory;
use App\Service\Habit\HabitCompletion\HabitCompletionGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HabitResponseDtoFactoryTest extends TestCase
{
    private HabitResponseDtoFactory $factory;

    private HabitCompletionGenerator&MockObject $completionGenerator;

    protected function setUp(): void
    {
        $this->completionGenerator = $this->createMock(HabitCompletionGenerator::class);
        $this->factory = new HabitResponseDtoFactory($this->completionGenerator);
    }

    public function testCreateFromEntity(): void
    {
        $habit = $this->createHabit();
        $completions = [
            ['date' => '2026-03-07', 'completed' => true],
            ['date' => '2026-03-06', 'completed' => false],
        ];

        $this->completionGenerator
            ->expects($this->once())
            ->method('generateCompletions')
            ->with($habit)
            ->willReturn($completions);

        $dto = $this->factory->createFromEntity($habit);

        $this->assertSame('Test Habit', $dto->name);
        $this->assertSame([0, 1, 2, 3, 4, 5, 6], $dto->days);
        $this->assertSame('09:00', $dto->time);
        $this->assertSame('#8b5cf6', $dto->color);
        $this->assertCount(2, $dto->completions);
    }

    public function testCreateFromEntities(): void
    {
        $habit1 = $this->createHabit();
        $habit2 = $this->createHabit();

        $this->completionGenerator
            ->method('generateCompletions')
            ->willReturn([]);

        $dtos = $this->factory->createFromEntities([$habit1, $habit2]);

        $this->assertCount(2, $dtos);
    }

    private function createHabit(): Habit
    {
        $user = new User();
        $user->setTimezone(new \DateTimeZone('UTC'));

        $habit = new Habit();
        $habit->setUser($user);
        $habit->setDescription('Test Habit');
        $habit->setRemindWeekDays(127);
        $habit->setRemindAt(new \DateTimeImmutable('09:00'));

        return $habit;
    }
}
