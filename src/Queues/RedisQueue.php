<?php

declare(strict_types=1);

namespace Daycry\Queues\Queues;

use CodeIgniter\Config\Services;
use Config\Cache;
use Daycry\Queues\Exceptions\QueueException;
use Daycry\Queues\Interfaces\QueueInterface;
use Daycry\Queues\Interfaces\WorkerInterface;
use Daycry\Queues\Libraries\RedisHandler;
use Daycry\Queues\Job as QueuesJob;
use Redis;

class RedisQueue extends BaseQueue implements QueueInterface, WorkerInterface
{
    /**
     * Queue waiting for consumption
     */
    public const QUEUE_WAITING = '{redis-queue}-waiting';

    /**
     * Queue with delayed consumption
     */
    public const QUEUE_DELAYED = '{redis-queue}-delayed';

    /**
     * Queue with consumption failure
     */
    public const QUEUE_FAILED = '{redis-queue}-failed';

    protected Redis $connection;

    protected Redis $queue;

    private mixed $job = null;

    /**
     * @var array
     */
    protected $_options = [
        'retry_seconds' => 5,
        'max_attempts'  => 5,
        'auth'          => '',
        'db'            => 0
    ];

    public function __construct()
    {
        $cacheConfig = config(Cache::class);
        $cacheConfig->handler = 'redis';

        $cache = new RedisHandler($cacheConfig);
        $cache->initialize();
        $this->connection = $cache->getRedis();
        //$this->queue = $cache->getRedis();
        //$this->queue->brPoping = 0;
    }

    public function enqueue(object $data, string $queue = 'default')
    {
        $this->_options['max_attempts'] = service('settings')->get('Queue.maxAttempts');

        $parser = Services::parser();

        $this->calculateDelay($data);

        $now = $id = time();

        $content = \json_encode([
            'id'       => $id,
            'time'     => $now,
            'delay'    => $this->getDelay(),
            'data'     => $data
        ]);

        if ($this->getDelay() == 0) {
            $this->connection->lPush(service('settings')->get('Cache.prefix') . $parser->setData(['redis-queue' => $queue])->renderString(static::QUEUE_WAITING), $content);
        } else {
            $this->connection->zAdd(service('settings')->get('Cache.prefix') . $parser->setData(['redis-queue' => $queue])->renderString(static::QUEUE_DELAYED), $now + $this->getDelay(), $content);
        }

        return $id;
    }

    public function watch(string $queue)
    {
        $parser = Services::parser();

        $redisKey = service('settings')->get('Cache.prefix') . $parser->setData(['redis-queue' => $queue])->renderString(static::QUEUE_WAITING);

        $this->tryToPullDelayQueue($redisKey);

        return $this->pull($redisKey);
    }

    protected function pull(string $queue)
    {
        $job = $this->connection->rpop($queue);

        if($job) {
            return $this->job = \json_decode($job);
        }

        return false;
    }

    public function getDataJob()
    {
        return $this->job->data;
    }

    public function removeJob(QueuesJob $job, bool $recreate = false): bool
    {
        if($recreate === true) {
            //$this->connection->release($this->job);
            $job->addAttempt();
            $job->enqueue($job->getQueue());
        }
        $this->job = null;

        return true;
    }

    protected function tryToPullDelayQueue(string $queue)
    {
        $parser = Services::parser();

        $now = time();
        $options = ['LIMIT' => [0, 128]];

        $items = $this->connection->zrangebyscore(service('settings')->get('Cache.prefix') . $parser->setData(['redis-queue' => $queue])->renderString(static::QUEUE_DELAYED), '-inf', strval($now), $options);

        if ($items === false) {
            throw QueueException::forInvalidConnection($this->connection->error());
        }

        foreach ($items as $packageStr) {
            $this->connection->zRem(service('settings')->get('Cache.prefix') . $parser->setData(['redis-queue' => $queue])->renderString(static::QUEUE_DELAYED), $packageStr, function ($result) use ($packageStr, $queue, $parser) {
                if ($result !== 1) {
                    return;
                }
                $package = json_decode($packageStr);
                if (!$package) {
                    $this->connection->lPush(service('settings')->get('Cache.prefix') . $parser->setData(['redis-queue' => $queue])->renderString(static::QUEUE_FAILED), $packageStr);
                }
                $this->connection->lPush(service('settings')->get('Cache.prefix') . $parser->setData(['redis-queue' => $queue])->renderString(static::QUEUE_WAITING), $packageStr);
            });
        }
    }
}
