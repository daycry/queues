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

namespace Daycry\Queues\Queues;

use Daycry\Queues\Interfaces\QueueInterface;
use Daycry\Queues\Job;

class SyncQueue extends BaseQueue implements QueueInterface
{
    public function enqueue(object $data, string $queue = 'default')
    {
        $job = new Job($data);

        return $job->run();
    }
}
