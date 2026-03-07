<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Service\Keyboard\HabitMenuInlineKeyboard;
use App\Translator\NoTranslator;
use PHPUnit\Framework\TestCase;

class HabitMenuInlineKeyboardTest extends TestCase
{
    private HabitMenuInlineKeyboard $keyboard;

    protected function setUp(): void
    {
        $this->keyboard = new HabitMenuInlineKeyboard(new NoTranslator());
    }

    public function testGenerateWithNoHabitsShowsOnlyAddButton(): void
    {
        $result = $this->keyboard->generate([]);

        $rows = $result->inlineKeyboard;
        $this->assertCount(1, $rows);
        $this->assertStringContainsString('habit.menu.add_new_habit', $rows[0][0]->text);
    }

    public function testGenerateWithHabitsShowsAddAndListButtons(): void
    {
        $result = $this->keyboard->generate(['habit1']);

        $rows = $result->inlineKeyboard;
        $this->assertCount(2, $rows);
        $this->assertStringContainsString('habit.menu.add_new_habit', $rows[0][0]->text);
        $this->assertStringContainsString('habit.menu.my_habits', $rows[1][0]->text);
    }
}
