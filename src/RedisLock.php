<?php declare(strict_types=1);

namespace YaSD\Redis;

use Redis;

class RedisLock
{
    public function __construct(
        protected Redis $redis,
        protected ?string $keyPrefix = null,
    ) {
    }

    public function lock(string $resource, int $ttlInMs): ?array
    {
        $token = static::genRandomToken();
        $validity = $ttlInMs;

        if ($ttlInMs > 0 && $this->doLock($this->getKey($resource), $token, $ttlInMs)) {
            return compact('resource', 'token', 'validity');
        }
        return null;
    }

    public function release(array $lock): void
    {
        $this->doRelease($this->getKey($lock['resource']), $lock['token']);
    }

    protected function doLock(string $resource, string $token, int $ttlInMs): bool
    {
        return (bool) $this->redis->set($resource, $token, ['NX', 'PX' => $ttlInMs]);
    }

    protected function doRelease(string $resource, string $token): bool
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';
        return (bool) $this->redis->eval($script, [$resource, $token], 1);
    }

    protected function getKey(string $key): string
    {
        return $this->keyPrefix ? $this->keyPrefix . $key : $key;
    }

    protected static function genRandomToken(): string
    {
        return bin2hex(random_bytes(5));
    }
}
