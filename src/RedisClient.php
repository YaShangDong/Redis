<?php declare(strict_types=1);

namespace YaSD\Redis;

use Redis;
use RedisException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RedisClient
{
    protected Redis $redis;
    protected array $config;

    protected function __construct(
        array $config,
        protected bool $persistent = false
    ) {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->config = $resolver->resolve($config);

        $this->connect();
    }

    public static function create(array $config = []): static
    {
        return new static($config, false);
    }

    public static function createPersistent(array $config = []): static
    {
        return new static($config, true);
    }

    public function ping(): static
    {
        try {
            if (true !== $this->redis->ping()) {
                $this->reconnect();
            }
        } catch (RedisException $e) {
            $this->reconnect();
        }
        return $this;
    }

    public function getRedis(bool $ping = false): Redis
    {
        if ($ping) $this->ping();
        return $this->redis;
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'host'          => '127.0.0.1',
            'port'          => 6379,
            'timeout'       => 0.0,
            'persistentId'  => null,
            'retryInterval' => 0,
            'readTimeout'   => 0.0,
            'auth'          => null,
        ]);

        $resolver->setAllowedTypes('host', 'string');
        $resolver->setAllowedTypes('port', 'int');
        $resolver->setAllowedTypes('timeout', 'float');
        $resolver->setAllowedTypes('persistentId', ['null', 'string']);
        $resolver->setAllowedTypes('retryInterval', 'int');
        $resolver->setAllowedTypes('readTimeout', 'float');
        $resolver->setAllowedTypes('auth', ['null', 'string', 'array']);
    }

    protected function connect(): void
    {
        $this->redis = new Redis();

        if ($this->persistent) {
            $this->redis->pconnect($this->config['host'], $this->config['port'], $this->config['timeout'], $this->config['persistentId'], $this->config['retryInterval'], $this->config['readTimeout']);
        } else {
            $this->redis->connect($this->config['host'], $this->config['port'], $this->config['timeout'], null, $this->config['retryInterval'], $this->config['readTimeout']);
        }

        if ($this->config['auth']) $this->redis->auth($this->config['auth']);
    }

    protected function reconnect(): void
    {
        $this->redis->close();
        $this->connect();
    }
}
