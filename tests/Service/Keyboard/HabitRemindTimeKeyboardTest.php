<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Service\Keyboard\HabitRemindTimeInlineKeyboard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use TgBotApi\BotApiBase\Type\KeyboardButtonType;

class HabitRemindTimeKeyboardTest extends TestCase
{
    /**
     * @test
     */
    public function itCreatesValidTimeKeyboardTest(): void
    {
        $habitRemindTimeInlineKeyboard = new HabitRemindTimeInlineKeyboard();

        $expectedRowFirst = ['06:00', '07:00', '08:00', '09:00', '10:00', '11:00'];
        $expectedRowSecond = ['12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
        $expectedRowThird = ['18:00', '19:00', '20:00', '21:00', '22:00', '23:00'];

        $keyboard = $habitRemindTimeInlineKeyboard->generate(Uuid::v4()->toRfc4122());
        $this->assertIsArray($keyboard->inlineKeyboard);

        $row = $keyboard->inlineKeyboard[0];
        $this->assertIsArray($row);
        $this->assertEquals($expectedRowFirst, $this->getButtonLabels($row));

        $row = $keyboard->inlineKeyboard[1];
        $this->assertIsArray($row);
        $this->assertEquals($expectedRowSecond, $this->getButtonLabels($row));

        $row = $keyboard->inlineKeyboard[2];
        $this->assertIsArray($row);
        $this->assertEquals($expectedRowThird, $this->getButtonLabels($row));
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