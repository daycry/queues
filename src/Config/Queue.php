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

namespace Daycry\Queues\Config;

use CodeIgniter\Config\BaseConfig;
use Daycry\Queues\Queues\BeanstalkQueue;
use Daycry\Queues\Queues\DatabaseQueue;
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
        'classes',
    ];
    public array|string $queues        = 'default,dummy';
    public string $worker              = 'sync';
    public int $maxAttempts            = 5;
    public int $waitingTimeBetweenJobs = 2;
    public array $workers              = [
        'sync'       => SyncQueue::class,
        'beanstalk'  => BeanstalkQueue::class,
        'redis'      => RedisQueue::class,
        'serviceBus' => ServiceBusQueue::class,
        'database'   => DatabaseQueue::class,
    ];
    public array $beanstalk = [
        'host' => '127.0.0.1',
        'port' => 11300,
    ];
    public array $serviceBus = [
        'url'    => '',
        'issuer' => '',
        'secret' => '',
    ];
    public array $database = [
        'group' => null,
        'table' => 'queues',
    ];
}
