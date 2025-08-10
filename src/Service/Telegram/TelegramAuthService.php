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
        unset($data['hash']);

        ksort($data);
        $checkString = '';

        foreach ($data as $k => $v) {
            $checkString .= $k . '=' . $v;
        }

        $secretKey = hash('sha256', $this->token, true);
        $hash = hash_hmac('sha256', $checkString, $secretKey);

        if (!hash_equals($hash, $checkHash) || (time() - $data['auth_date'] > 86400)) {
            return null;
        }

        $userData = json_decode($data['user'], true);

        if (!isset($userData['id'])) {
            return null;
        }

        return new TelegramUser(
            $userData['id'],
            $userData['first_name'] ?? '',
            $userData['last_name'] ?? '',
            $userData['username'] ?? '',
            $userData['language_code'] ?? '',
        );
    }
}
