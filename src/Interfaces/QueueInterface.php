<?php

declare(strict_types=1);

namespace Daycry\Queues\Interfaces;

interface QueueInterface
{
    public function enqueue(object $data, string $queue = 'default');
}
