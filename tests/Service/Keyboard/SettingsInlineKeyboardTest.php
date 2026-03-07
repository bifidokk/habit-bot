<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Service\Keyboard\SettingsInlineKeyboard;
use App\Translator\NoTranslator;
use PHPUnit\Framework\TestCase;

class SettingsInlineKeyboardTest extends TestCase
{
    private SettingsInlineKeyboard $keyboard;

    protected function setUp(): void
    {
        $this->keyboard = new SettingsInlineKeyboard(new NoTranslator());
    }

    public function testGenerateReturnsTimezoneAndLanguageButtons(): void
    {
        $result = $this->keyboard->generate();

        $rows = $result->inlineKeyboard;
        $this->assertCount(2, $rows);
        $this->assertStringContainsString('settings_menu.timezone', $rows[0][0]->text);
        $this->assertStringContainsString('settings_menu.language', $rows[1][0]->text);
    }
}
