<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Service\Keyboard\HabitRemindDayInlineKeyboard;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class HabitRemindDayKeyboardTest extends TestCase
{
    /**
     * @test
     */
    public function itCreatesValidDayPeriodKeyboardTest(): void
    {
        $days = '0100100';
        $actualDays = '';
        $keyboard = HabitRemindDayInlineKeyboard::generate((int) bindec($days), Uuid::v4()->toRfc4122());

        $daysRow = $keyboard->inlineKeyboard[0];

        foreach ($daysRow as $dayButton) {
            if (strpos($dayButton->text, HabitRemindDayInlineKeyboard::MARK_CODE) !== false) {
                $actualDays .= '1';
            } else {
                $actualDays .= '0';
            }
        }

        $this->assertEquals($days, $actualDays);
    }
}
