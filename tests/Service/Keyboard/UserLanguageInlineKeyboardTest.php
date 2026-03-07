<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Service\Keyboard\UserLanguageInlineKeyboard;
use App\Translator\NoTranslator;
use PHPUnit\Framework\TestCase;

class UserLanguageInlineKeyboardTest extends TestCase
{
    private UserLanguageInlineKeyboard $keyboard;

    protected function setUp(): void
    {
        $this->keyboard = new UserLanguageInlineKeyboard(new NoTranslator());
    }

    public function testGenerateReturnsTwoLanguageButtons(): void
    {
        $result = $this->keyboard->generate();

        $rows = $result->inlineKeyboard;
        $this->assertCount(2, $rows);
        $this->assertStringContainsString('settings_menu.languages.en', $rows[0][0]->text);
        $this->assertStringContainsString('settings_menu.languages.ru', $rows[1][0]->text);
    }

    public function testGenerateCallbackDataContainsLanguageCode(): void
    {
        $result = $this->keyboard->generate();

        $rows = $result->inlineKeyboard;
        $this->assertStringContainsString('lang=en', $rows[0][0]->callbackData);
        $this->assertStringContainsString('lang=ru', $rows[1][0]->callbackData);
    }
}
