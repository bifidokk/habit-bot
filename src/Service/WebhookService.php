<?php

declare(strict_types=1);

namespace App\Service;

class WebhookService
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function isTokenValid(string $token): bool
    {
        return $token === $this->token;
    }
}
