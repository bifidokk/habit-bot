<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Entity\Habit;
use App\Service\Keyboard\EmojiCode;
use App\Service\Keyboard\HabitInlineKeyboard;
use App\Translator\NoTranslator;
use PHPUnit\Framework\TestCase;
use TgBotApi\BotApiBase\Type\InlineKeyboardButtonType;

class HabitInlineKeyboardTest extends TestCase
{
    private HabitInlineKeyboard $habitInlineKeyboard;

    protected function setUp(): void
    {
        parent::setUp();

        $translator = new NoTranslator();
        $this->habitInlineKeyboard = new HabitInlineKeyboard($translator);
    }

    public function testItCreatesHabitKeyboardTest(): void
    {
        $habit = new Habit();
        $keyboard = $this->habitInlineKeyboard->generate($habit);

        $rows = $keyboard->inlineKeyboard;
        $rowCount = 0;
        $steps = [];

        foreach ($this->habitInlineKeyboard->getSteps($habit) as $description) {
            $steps[] = $description;
        }

        foreach ($rows as $row) {
            $button = $row[0];
            $this->assertInstanceOf(InlineKeyboardButtonType::class, $button);

            $description = $steps[$rowCount];
            $this->assertStringContainsString($description, $button->text);

            ++$rowCount;
        }
    }

    public function testItMarksDescriptionAddedTest(): void
    {
        $habit = new Habit();
        $habit->setDescription('Hello');
        $keyboard = $this->habitInlineKeyboard->generate($habit);

        $rows = $keyboard->inlineKeyboard;
        $button = $rows[0][0];
        $this->assertStringContainsString(EmojiCode::Marked->value, $button->text);
    }

    public function testItMarksRemindDaysAddedTest(): void
    {
        $habit = new Habit();
        $habit->setRemindWeekDays(4);
        $keyboard = $this->habitInlineKeyboard->generate($habit);

        $rows = $keyboard->inlineKeyboard;
        $button = $rows[1][0];
        $this->assertStringContainsString(EmojiCode::Marked->value, $button->text);
    }

    public function testItMarksRemindTimeAddedTest(): void
    {
        $habit = new Habit();
        $habit->setRemindAt(new \DateTimeImmutable());
        $keyboard = $this->habitInlineKeyboard->generate($habit);

        $rows = $keyboard->inlineKeyboard;
        $button = $rows[2][0];
        $this->assertStringContainsString(EmojiCode::Marked->value, $button->text);
    }
}
