<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Service\Keyboard\EmojiCode;
use App\Service\Keyboard\HabitRemindDayInlineKeyboard;
use App\Translator\NoTranslator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class HabitRemindDayKeyboardTest extends TestCase
{
    public function testItCreatesValidDayPeriodKeyboardTest(): void
    {
        $days = '0100100';
        $actualDays = '';

        $habitRemindDayInlineKeyboard = new HabitRemindDayInlineKeyboard(new NoTranslator());
        $keyboard = $habitRemindDayInlineKeyboard->generate((int) bindec($days), Uuid::v4()->toRfc4122());

        $daysRow = $keyboard->inlineKeyboard[0];

        foreach ($daysRow as $dayButton) {
            if (strpos($dayButton->text, EmojiCode::Marked->value) !== false) {
                $actualDays .= '1';
            } else {
                $actualDays .= '0';
            }
        }

        $this->assertEquals($days, $actualDays);
    }
}
