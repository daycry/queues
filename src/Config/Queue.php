<?php

declare(strict_types=1);

namespace Daycry\Queues\Config;

use CodeIgniter\Config\BaseConfig;
use Daycry\Queues\Queues\BeanstalkQueue;
use Daycry\Queues\Queues\RedisQueue;
use Daycry\Queues\Queues\ServiceBusQueue;
use Daycry\Queues\Queues\SyncQueue;

class Queue extends BaseConfig
{
    public array $jobTypes = [
        'command',
        'shell',
        'event',
        'url',
        'classes'
    ];

    public string|array $queues = 'default,dummy';

    public string $worker = 'sync';

    public int $maxAttempts = 5;

    public int $waitingTimeBetweenJobs = 2;

    public array $workers = [
        'sync' => SyncQueue::class,
        'beanstalk' => BeanstalkQueue::class,
        'redis' => RedisQueue::class,
        'serviceBus' => ServiceBusQueue::class
    ];

    public array $beanstalk = [
        'host' => '127.0.0.1',
        'port' => 11300
    ];

    public array $serviceBus = [
        'url' => '',
        'issuer' => '',
        'secret' => ''
    ];
}
