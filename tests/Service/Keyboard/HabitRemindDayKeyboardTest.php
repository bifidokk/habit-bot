<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Service\Keyboard\HabitRemindDayKeyboard;
use PHPUnit\Framework\TestCase;

class HabitRemindDayKeyboardTest extends TestCase
{
    /**
     * @test
     */
    public function itCreatesValidDayPeriodKeyboardTest(): void
    {
        $days = '0100100';
        $actualDays = '';
        $keyboard = HabitRemindDayKeyboard::generate((int) bindec($days));

        $daysRow = $keyboard->keyboard[0];

        foreach ($daysRow as $dayButton) {
            if (strpos($dayButton->text, HabitRemindDayKeyboard::MARK_CODE) !== false) {
                $actualDays .= '1';
            } else {
                $actualDays .= '0';
            }
        }

        $this->assertEquals($days, $actualDays);
    }
}
