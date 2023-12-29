<?php

declare(strict_types=1);

namespace Daycry\Queues\Queues;

use Daycry\Queues\Interfaces\QueueInterface;
use Daycry\Queues\Job;

class SyncQueue implements QueueInterface
{
    public function enqueue(object $data, string $queue = 'default')
    {
        $job = new Job($data);
        return $job->run();
    }
}
