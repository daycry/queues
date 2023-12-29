<?php

namespace Daycry\Queues\Libraries;

use CodeIgniter\Cache\Handlers\RedisHandler as HandlersRedisHandler;
use Config\Cache;

class RedisHandler extends HandlersRedisHandler
{
    /**
     * Note: Use `CacheFactory::getHandler()` to instantiate.
     */
    public function __construct(Cache $config)
    {
        $this->prefix = $config->prefix;

        $this->config = array_merge($this->config, $config->redis);
    }

    public function getRedis()
    {
        return $this->redis;
    }
}
