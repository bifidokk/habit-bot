<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Keyboard\HabitPreviewInlineKeyboard;
use App\Translator\NoTranslator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class HabitPreviewInlineKeyboardTest extends TestCase
{
    private HabitPreviewInlineKeyboard $keyboard;

    protected function setUp(): void
    {
        $this->keyboard = new HabitPreviewInlineKeyboard(new NoTranslator());
    }

    public function testGenerateReturnsBackAndSubmitButtons(): void
    {
        $habit = $this->createHabitWithId();

        $result = $this->keyboard->generate($habit);

        $rows = $result->inlineKeyboard;
        $habitId = $habit->getId()->toRfc4122();

        $this->assertCount(3, $rows);
        $this->assertCount(1, $rows[0]);
        $this->assertCount(1, $rows[1]);
        $this->assertCount(1, $rows[2]);
        $this->assertStringContainsString('back', $rows[0][0]->text);
        $this->assertStringContainsString('cancel', $rows[1][0]->text);
        $this->assertStringContainsString('submit', $rows[2][0]->text);
        $this->assertStringContainsString($habitId, $rows[0][0]->callbackData);
        $this->assertStringContainsString($habitId, $rows[1][0]->callbackData);
        $this->assertStringContainsString($habitId, $rows[2][0]->callbackData);
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
