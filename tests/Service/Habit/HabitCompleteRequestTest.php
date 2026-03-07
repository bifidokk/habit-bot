<?php

declare(strict_types=1);

namespace App\Tests\Service\Habit;

use App\Service\Habit\HabitCompletion\HabitCompleteRequest;
use PHPUnit\Framework\TestCase;

class HabitCompleteRequestTest extends TestCase
{
    public function testGetDateAsDateTimeImmutableValid(): void
    {
        $request = new HabitCompleteRequest(date: '2026-03-07');

        $result = $request->getDateAsDateTimeImmutable();

        $this->assertInstanceOf(\DateTimeImmutable::class, $result);
        $this->assertSame('2026-03-07', $result->format('Y-m-d'));
    }

    public function testGetDateAsDateTimeImmutableInvalid(): void
    {
        $request = new HabitCompleteRequest(date: 'invalid');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid date string');

        $request->getDateAsDateTimeImmutable();
    }
}
