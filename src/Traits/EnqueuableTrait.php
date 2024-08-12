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

namespace Daycry\Queues\Traits;

use DateInterval;
use DateTime;
use Daycry\Queues\Exceptions\JobException;
use Daycry\Queues\Exceptions\QueueException;
use Daycry\Queues\Interfaces\QueueInterface;
use Daycry\Queues\Libraries\Utils;

trait EnqueuableTrait
{
    protected int $attempts  = 0;
    protected ?string $queue = null;
    private QueueInterface $worker;

    public function enqueue(?string $queue = null)
    {
        $queues = Utils::parseConfigFile(service('settings')->get('Queue.queues'));

        $queue ??= $this->queue;

        if ($queue === null) {
            $this->setToDefaultQueue();
            $queue = $this->queue;
        }

        if (! in_array($queue, $queues, true)) {
            throw QueueException::forInvalidQueue($queue);
        }

        $object        = $this->toObject();
        $object->queue = $queue;

        Utils::checkDataQueue($object, 'queueData');

        Utils::checkDataQueue($object->action, $this->type);

        return $this->worker->enqueue($object, $queue);
    }

    /**
     * Returns the attempts.
     */
    public function getAttempt(): int
    {
        return $this->attempts;
    }

    /**
     * Add attempts.
     *
     * @return int
     */
    public function addAttempt(): self
    {
        $this->attempts++;

        if ($this->schedule !== null) {
            $this->scheduled((new DateTime())->add(new DateInterval('PT1H')));
        }

        return $this;
    }

    /**
     * Set queue.
     */
    public function setQueue(string $queue): self
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * Get queue.
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    public function setToDefaultQueue(): self
    {
        $queues = Utils::parseConfigFile(service('settings')->get('Queue.queues'));

        $this->queue = $queues[0];

        return $this;
    }

    /**
     * Provide magic function-access to Workers to run jobs.
     *
     * @param list<string> $args
     *
     * @throws JobException
     */
    public function __call(string $method, array $args)
    {
        if ($this->worker && method_exists($this->worker, $method)) {
            return $this->worker->{$method}(...$args);
        }

        throw JobException::forInvalidMethod($method);
    }

    protected function checkWorker(): void
    {
        $workers = service('settings')->get('Queue.workers');
        $worker  = service('settings')->get('Queue.worker');

        if (! array_key_exists($worker, $workers)) {
            throw QueueException::forInvalidWorker($worker);
        }

        $this->worker = new $workers[$worker]();
    }
}
