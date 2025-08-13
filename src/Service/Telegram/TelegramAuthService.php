<?php

declare(strict_types=1);

namespace App\Service\Telegram;

class TelegramAuthService
{
    public function __construct(
        private readonly string $token,
    ) {
    }

    public function verify(string $initData): ?TelegramUser
    {
        parse_str($initData, $data);
        $checkHash = $data['hash'] ?? '';

        if (! is_string($checkHash)) {
            return null;
        }

        unset($data['hash']);

        ksort($data);
        $checkString = '';

        foreach ($data as $key => $value) {
            // cast to string
            $checkString .= sprintf('%s=%s', $key, is_array($value) ? json_encode($value) : $value);
        }

        $secretKey = hash('sha256', $this->token, true);
        $hash = hash_hmac('sha256', $checkString, $secretKey);

        if (! isset($data['auth_date']) || ! is_numeric($data['auth_date'])) {
            return null;
        }

        if (! hash_equals($hash, $checkHash) || (time() - (int) $data['auth_date'] > 86400)) {
            return null;
        }

        if (! isset($data['user']) || ! is_string($data['user'])) {
            return null;
        }

        $userData = json_decode($data['user'], true);

        if (! isset($userData['id'])) {
            return null;
        }

        return new TelegramUser(
            (string) $userData['id'],
            $userData['first_name'] ?? '',
            $userData['last_name'] ?? '',
            $userData['username'] ?? '',
            $userData['language_code'] ?? '',
        );
    }
}
