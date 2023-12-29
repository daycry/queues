<?php

declare(strict_types=1);

namespace Daycry\Queues\Interfaces;

use Daycry\Queues\Job;

interface WorkerInterface
{
    public function watch(string $queue);
    public function getDataJob();
    public function removeJob(Job $job, bool $recreate = false): bool;
}
