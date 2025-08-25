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

        if (! isset($data['auth_date'])
            || ! is_numeric($data['auth_date'])
        ) {
            return null;
        }

        if (! $this->isSafe($initData)
            || (time() - (int) $data['auth_date'] > 86400)
        ) {
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

    public function isSafe(string $initData): bool
    {
        [$checksum, $sortedInitData] = $this->convertInitData($initData);
        $secretKey = hash_hmac('sha256', $this->token, 'WebAppData', true);
        $hash = bin2hex(hash_hmac('sha256', $sortedInitData, $secretKey, true));

        return 0 === strcmp($hash, $checksum);
    }

    private function convertInitData(string $initData): array
    {
        $initDataArray = explode('&', rawurldecode($initData));
        $needle = 'hash=';
        $hash  = '';

        foreach ($initDataArray as &$data) {
            if (substr($data, 0, \strlen($needle)) === $needle) {
                $hash = substr_replace($data, '', 0, \strlen($needle));
                $data = null;
            }
        }

        $initDataArray = array_filter($initDataArray);
        sort($initDataArray);

        return [$hash, implode("\n", $initDataArray)];
    }
}
