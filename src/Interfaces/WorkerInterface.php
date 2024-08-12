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

namespace Daycry\Queues\Interfaces;

use Daycry\Queues\Job;

interface WorkerInterface
{
    public function watch(string $queue);

    public function getDataJob();

    public function removeJob(Job $job, bool $recreate = false): bool;
}
