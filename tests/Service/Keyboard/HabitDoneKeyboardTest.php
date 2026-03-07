<?php

declare(strict_types=1);

namespace App\Tests\Service\Keyboard;

use App\Entity\Habit;
use App\Entity\User;
use App\Service\Keyboard\HabitDoneKeyboard;
use App\Translator\NoTranslator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class HabitDoneKeyboardTest extends TestCase
{
    private HabitDoneKeyboard $keyboard;

    protected function setUp(): void
    {
        $this->keyboard = new HabitDoneKeyboard(new NoTranslator());
    }

    public function testGenerateReturnsDoneAndLaterButtons(): void
    {
        $user = new User();
        $user->setLanguageCode('en');

        $habit = new Habit();
        $habit->setUser($user);
        $reflection = new \ReflectionClass($habit);
        $property = $reflection->getProperty('id');
        $property->setValue($habit, Uuid::v4());

        $result = $this->keyboard->generate($habit);

        $rows = $result->inlineKeyboard;
        $this->assertCount(1, $rows);
        $this->assertCount(2, $rows[0]);
        $this->assertStringContainsString('done', $rows[0][0]->text);
        $this->assertStringContainsString('later', $rows[0][1]->text);
        $this->assertStringContainsString('/done', $rows[0][0]->callbackData);
        $this->assertStringContainsString('/busy', $rows[0][1]->callbackData);
    }
}
