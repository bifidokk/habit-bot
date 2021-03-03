<?php

declare(strict_types=1);

namespace App\Service\Command;

use App\Service\Webhook\WebhookMessage;

interface CommandInterface
{
    public function run(WebhookMessage $message): void;
}
