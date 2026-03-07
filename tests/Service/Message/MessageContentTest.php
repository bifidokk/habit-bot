<?php

declare(strict_types=1);

namespace App\Tests\Service\Message;

use App\Service\Message\MessageContent;
use PHPUnit\Framework\TestCase;

class MessageContentTest extends TestCase
{
    public function testEscapeRemovesPlusAndExclamation(): void
    {
        $messageContent = new MessageContent();

        $this->assertSame('HelloWorld', $messageContent->escapeMessageSymbols('Hello+World!'));
    }

    public function testEscapeNoSpecialChars(): void
    {
        $messageContent = new MessageContent();

        $this->assertSame('Hello', $messageContent->escapeMessageSymbols('Hello'));
    }
}
