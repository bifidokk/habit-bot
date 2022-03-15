<?php

declare(strict_types=1);

namespace App\Service\Redis;

class RedisClient implements RedisClientInterface
{
    public function __construct(private \Redis $redis) {}

    /**
     * @param mixed $value
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        return $this->redis->set($key, $value, $ttl);
    }

    /**
     * @return false|mixed|string
     */
    public function get(string $key)
    {
        return $this->redis->get($key);
    }

    public function del($key1, ...$otherKeys): int
    {
        return $this->redis->del($key1, ...$otherKeys);
    }
}
