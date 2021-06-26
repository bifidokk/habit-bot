<?php

declare(strict_types=1);

namespace App\Service\Redis;

class RedisDummyClient implements RedisClientInterface
{
    private array $data;

    /**
     * @param mixed $value
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        $this->data[$key] = $value;

        return true;
    }

    /**
     * @return false|mixed|string
     */
    public function get(string $key)
    {
        if (!isset($this->data[$key])) {
            return false;
        }

        return $this->data[$key];
    }

    public function del($key1, ...$otherKeys): int
    {
        unset($this->data[$key1]);

        return 1;
    }
}
