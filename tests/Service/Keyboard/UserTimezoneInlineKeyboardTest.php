<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Service\Keyboard\UserTimezoneInlineKeyboard;
use PHPUnit\Framework\TestCase;

class UserTimezoneInlineKeyboardTest extends TestCase
{
    private UserTimezoneInlineKeyboard $keyboard;

    protected function setUp(): void
    {
        $this->keyboard = new UserTimezoneInlineKeyboard();
    }

    public function testGenerateReturns24TimezonesIn6Rows(): void
    {
        $result = $this->keyboard->generate(new \DateTimeZone('UTC'));

        $rows = $result->inlineKeyboard;
        $this->assertCount(6, $rows);

        $totalButtons = 0;

        foreach ($rows as $row) {
            $this->assertCount(4, $row);
            $totalButtons += count($row);
        }
        $this->assertSame(24, $totalButtons);
    }

    public function testGenerateMarksCurrentTimezone(): void
    {
        $result = $this->keyboard->generate(new \DateTimeZone('UTC'));

        $rows = $result->inlineKeyboard;
        // UTC is first button
        $this->assertStringContainsString('UTC', $rows[0][0]->text);
        // Should have the marked emoji
        $this->assertStringContainsString('✅', $rows[0][0]->text);

        // Other buttons should NOT have the marked emoji
        $this->assertStringNotContainsString('✅', $rows[0][1]->text);
    }

    public function testGenerateCallbackDataContainsTimezone(): void
    {
        $result = $this->keyboard->generate(new \DateTimeZone('UTC'));

        $rows = $result->inlineKeyboard;
        $this->assertStringContainsString('tz=UTC', $rows[0][0]->callbackData);
        $this->assertStringContainsString('tz=%2B01%3A00', $rows[0][1]->callbackData);
    }
}
