<?php
declare(strict_types=1);

namespace App\Tests\Service\Telegram;

use App\Service\Telegram\TelegramAuthService;
use App\Service\Telegram\TelegramUser;
use PHPUnit\Framework\TestCase;

class TelegramAuthServiceTest extends TestCase
{
    private const BOT_TOKEN = 'TEST_BOT_TOKEN';

    public function testItReturnsUserOnValidInitData(): void
    {
        $user = [
            'id' => 12345,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'language_code' => 'en',
        ];

        $fields = [
            'auth_date' => time(),
            'user' => json_encode($user),
        ];

        $initData = $this->generateInitData($fields);

        $service = new TelegramAuthService(self::BOT_TOKEN);
        $result = $service->verify($initData);

        $this->assertInstanceOf(TelegramUser::class, $result);
        $this->assertSame($user['id'], $result->getId());
        $this->assertSame($user['first_name'], $result->getFirstName());
    }

    public function testItReturnsNullOnInvalidHash(): void
    {
        $fields = [
            'auth_date' => time(),
            'user' => json_encode(['id' => 1]),
            'hash' => 'invalid_hash',
        ];

        $initData = http_build_query($fields);

        $service = new TelegramAuthService(self::BOT_TOKEN);
        $result = $service->verify($initData);

        $this->assertNull($result);
    }

    public function testItReturnsNullOnExpiredAuthDate(): void
    {
        $fields = [
            'auth_date' => time() - 90000, // > 86400 seconds old
            'user' => json_encode(['id' => 1]),
        ];

        $initData = $this->generateInitData($fields);

        $service = new TelegramAuthService(self::BOT_TOKEN);
        $result = $service->verify($initData);

        $this->assertNull($result);
    }

    private function generateInitData(array $fields): string
    {
        ksort($fields);
        $checkString = '';

        foreach ($fields as $k => $v) {
            $checkString .= $k . '=' . $v;
        }

        $secretKey = hash('sha256', self::BOT_TOKEN, true);
        $hash = hash_hmac('sha256', $checkString, $secretKey);

        $fields['hash'] = $hash;

        return http_build_query($fields);
    }
}
