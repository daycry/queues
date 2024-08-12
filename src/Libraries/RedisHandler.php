<?php

declare(strict_types=1);

/**
 * This file is part of Daycry Queues.
 *
 * (c) Daycry <daycry9@proton.me>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

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
