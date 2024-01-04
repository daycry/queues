<?php

declare(strict_types=1);

namespace Daycry\Queues\Traits;

use DateInterval;
use DateTime;
use Daycry\Queues\Exceptions\JobException;
use Daycry\Queues\Exceptions\QueueException;
use Daycry\Queues\Interfaces\QueueInterface;
use Daycry\Queues\Libraries\Utils;

trait EnqueuableTrait
{
    protected int $attempts = 0;

    protected ?string $queue = null;

    private QueueInterface $worker;

    public function enqueue(?string $queue = null)
    {
        $queues = Utils::parseConfigFile(service('settings')->get('Queue.queues'));

        $queue = $queue ?? $this->queue;

        if(!in_array($queue, $queues)) {
            throw QueueException::forInvalidQueue($queue);
        }

        $object = $this->toObject();
        $object->queue = $queue;

        Utils::checkDataQueue($object, 'queueData');

        Utils::checkDataQueue($object->action, $this->type);

        return $this->worker->enqueue($object, $queue);
    }

    /**
     * Returns the attempts.
     *
     * @return int
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
        $this->attempts = $this->attempts + 1;

        if($this->schedule != null) {
            $this->scheduled((new DateTime())->add(new DateInterval('PT1H')));
        }

        return $this;
    }

    /**
     * Set queue.
     *
     * @return self
     */
    public function setQueue(string $queue): self
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * Get queue.
     *
     * @return string
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * Provide magic function-access to Workers to run jobs.
     *
     * @param string[] $args
     *
     * @throws JobException
     */
    public function __call(string $method, array $args)
    {
        if ($this->worker && method_exists($this->worker, $method)) {
            return $this->worker->{$method}(...$args);
        } else {
            throw JobException::forInvalidMethod($method);
        }
    }

    protected function checkWorker()
    {
        $workers = service('settings')->get('Queue.workers');
        $worker = service('settings')->get('Queue.worker');

        if(!array_key_exists($worker, $workers)) {
            throw QueueException::forInvalidWorker($worker);
        }

        $this->worker = new $workers[$worker]();
    }
}
