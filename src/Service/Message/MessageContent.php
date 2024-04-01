<?php
declare(strict_types=1);

namespace App\Service\Message;

class MessageContent
{
    public function escapeMessageSymbols(string $message): string
    {
        return str_replace(
            ['+', '!'],
            '',
            $message
        );
    }
}
