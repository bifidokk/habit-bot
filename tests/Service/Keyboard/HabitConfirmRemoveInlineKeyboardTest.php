<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Keyboard\HabitConfirmRemoveInlineKeyboard;
use App\Translator\NoTranslator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class HabitConfirmRemoveInlineKeyboardTest extends TestCase
{
    private HabitConfirmRemoveInlineKeyboard $keyboard;

    protected function setUp(): void
    {
        $this->keyboard = new HabitConfirmRemoveInlineKeyboard(new NoTranslator());
    }

    public function testGenerateReturnsYesAndNoButtons(): void
    {
        $habit = $this->createHabitWithId();

        $result = $this->keyboard->generate($habit);

        $rows = $result->inlineKeyboard;
        $this->assertCount(1, $rows);
        $this->assertCount(2, $rows[0]);
        $this->assertStringContainsString('yes', $rows[0][0]->text);
        $this->assertStringContainsString('no', $rows[0][1]->text);
        $this->assertStringContainsString('c=1', $rows[0][0]->callbackData);
        $this->assertStringContainsString('c=0', $rows[0][1]->callbackData);
        $this->assertStringContainsString((string) $habit->getId(), $rows[0][0]->callbackData);
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
