<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Keyboard\HabitViewInlineKeyboard;
use App\Translator\NoTranslator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class HabitViewInlineKeyboardTest extends TestCase
{
    private HabitViewInlineKeyboard $keyboard;

    protected function setUp(): void
    {
        $this->keyboard = new HabitViewInlineKeyboard(new NoTranslator());
    }

    public function testGenerateFirstPageSingleHabit(): void
    {
        $habit = $this->createHabitWithId();

        $result = $this->keyboard->generate($habit, 0, false);

        $rows = $result->inlineKeyboard;
        $this->assertCount(1, $rows);
        // Only remove button, no prev/next
        $this->assertCount(1, $rows[0]);
        $this->assertStringContainsString('remove', $rows[0][0]->text);
    }

    public function testGenerateFirstPageWithNext(): void
    {
        $habit = $this->createHabitWithId();

        $result = $this->keyboard->generate($habit, 0, true);

        $rows = $result->inlineKeyboard;
        $this->assertCount(1, $rows);
        // Remove + Next buttons
        $this->assertCount(2, $rows[0]);
        $this->assertStringContainsString('remove', $rows[0][0]->text);
        $this->assertStringContainsString('next', $rows[0][1]->text);
    }

    public function testGenerateMiddlePage(): void
    {
        $habit = $this->createHabitWithId();

        $result = $this->keyboard->generate($habit, 1, true);

        $rows = $result->inlineKeyboard;
        $this->assertCount(1, $rows);
        // Prev + Remove + Next
        $this->assertCount(3, $rows[0]);
        $this->assertStringContainsString('previous', $rows[0][0]->text);
        $this->assertStringContainsString('remove', $rows[0][1]->text);
        $this->assertStringContainsString('next', $rows[0][2]->text);
    }

    public function testGenerateLastPage(): void
    {
        $habit = $this->createHabitWithId();

        $result = $this->keyboard->generate($habit, 2, false);

        $rows = $result->inlineKeyboard;
        $this->assertCount(1, $rows);
        // Prev + Remove
        $this->assertCount(2, $rows[0]);
        $this->assertStringContainsString('previous', $rows[0][0]->text);
        $this->assertStringContainsString('remove', $rows[0][1]->text);
    }

    private function createHabitWithId(): Habit
    {
        $habit = new Habit();
        $habit->setUser(new User());
        $reflection = new \ReflectionClass($habit);
        $property = $reflection->getProperty('id');
        $property->setValue($habit, Uuid::v4());

        return $habit;
    }
}
