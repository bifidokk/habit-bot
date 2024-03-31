<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Service\Keyboard\HabitRemindTimeInlineKeyboard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use TgBotApi\BotApiBase\Type\KeyboardButtonType;

class HabitRemindTimeKeyboardTest extends TestCase
{
    public function testItCreatesValidTimeKeyboardTest(): void
    {
        $habitRemindTimeInlineKeyboard = new HabitRemindTimeInlineKeyboard();

        $expectedRow = [
            ['06:00', '07:00', '08:00'],
            ['09:00', '10:00', '11:00'],
            ['12:00', '13:00', '14:00'],
            ['15:00', '16:00', '17:00'],
            ['18:00', '19:00', '20:00'],
            ['21:00', '22:00', '23:00'],
        ];

        $keyboard = $habitRemindTimeInlineKeyboard->generate(Uuid::v4()->toRfc4122());
        $this->assertIsArray($keyboard->inlineKeyboard);

        foreach ($keyboard->inlineKeyboard as $key => $row) {
            $this->assertIsArray($row);
            $this->assertEquals($expectedRow[$key], $this->getButtonLabels($row));
        }
    }

    private function getButtonLabels(array $buttons): array
    {
        $labels = [];

        /** @var KeyboardButtonType $button */
        foreach ($buttons as $button) {
            $labels[] = $button->text;
        }

        return $labels;
    }
}
