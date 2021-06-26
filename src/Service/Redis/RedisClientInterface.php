<?php

declare(strict_types=1);

namespace App\Service\Redis;

interface RedisClientInterface
{
    /**
     * @param mixed $value
     */
    public function set(string $key, $value, int $ttl = null): bool;

    /**
     * @return false|mixed|string
     */
    public function get(string $key);

    public function del($key1, ...$otherKeys): int;
}
