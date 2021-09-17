# YaSD/Redis

```shell
composer require yasd/redis
```

```php
use YaSD\Redis\RedisClient;

$redisConfig = [
    'host' => '127.0.0.1',
    'port' => 6379,
    'auth' => 'auth string or user_pass array or null',
];

$redis = RedisClient::create($redisConfig);
$redis->getRedis()->doSomething();
$redis->getRedis(ping: true)->doSomething();
$redis->ping()->getRedis()->doSomething();
```

## Persistent

```php
use YaSD\Redis\RedisClient;

$redisConfig = [
    'host'         => '127.0.0.1',
    'port'         => 6379,
    'persistentId' => 'persistentId or null',
    'auth'         => 'auth string or user_pass array or null',
];

$redis = RedisClient::createPersistent($redisConfig);
$redis->ping()->getRedis()->doSomething();
```
