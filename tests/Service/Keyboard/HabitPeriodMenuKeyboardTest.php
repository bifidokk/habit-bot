<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Service\Keyboard\HabitPeriodMenuKeyboard;
use PHPUnit\Framework\TestCase;
use TgBotApi\BotApiBase\Type\KeyboardButtonType;

class HabitPeriodMenuKeyboardTest extends TestCase
{
    /**
     * @test
     */
    public function itCreatesValidDayPeriodKeyboardTest(): void
    {
        $days = '0100100';
        $actualDays = '';
        $keyboard = HabitPeriodMenuKeyboard::generate(bindec($days));

        $daysRow = $keyboard->keyboard[0];

        /** @var KeyboardButtonType $dayButton */
        foreach ($daysRow as $dayButton) {
            if (strpos($dayButton->text, HabitPeriodMenuKeyboard::MARK_CODE) !== false) {
                $actualDays .= '1';
            } else {
                $actualDays .= '0';
            }
        }

        $this->assertEquals($days, $actualDays);
    }
}
