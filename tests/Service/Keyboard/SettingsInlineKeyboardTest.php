<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Entity\User;
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

    public function testGenerateReturnsTimezoneLanguageAndAnimationsButtons(): void
    {
        $user = new User();
        $result = $this->keyboard->generate($user);

        $rows = $result->inlineKeyboard;
        $this->assertCount(3, $rows);
        $this->assertStringContainsString('settings_menu.timezone', $rows[0][0]->text);
        $this->assertStringContainsString('settings_menu.language', $rows[1][0]->text);
        $this->assertStringContainsString('settings_menu.animations', $rows[2][0]->text);
    }

    public function testAnimationsButtonShowsEnabledStatus(): void
    {
        $user = new User();
        $result = $this->keyboard->generate($user);

        $this->assertStringContainsString('✅', $result->inlineKeyboard[2][0]->text);
    }

    public function testAnimationsButtonShowsDisabledStatus(): void
    {
        $user = new User();
        $user->toggleShowAnimations();
        $result = $this->keyboard->generate($user);

        $this->assertStringContainsString('☑️', $result->inlineKeyboard[2][0]->text);
    }
}
