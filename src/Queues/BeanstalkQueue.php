<?php

declare(strict_types=1);

namespace Daycry\Queues\Queues;

use Daycry\Queues\Interfaces\QueueInterface;
use Daycry\Queues\Interfaces\WorkerInterface;
use Daycry\Queues\Job as QueuesJob;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\TubeName;
use Pheanstalk\Values\Job;

class BeanstalkQueue extends BaseQueue implements QueueInterface, WorkerInterface
{
    private int $priority = 0;
    private int $ttr = 3600;
    private ?Pheanstalk $connection = null;
    private ?Job $job = null;

    public function __construct()
    {
        $config = service('settings')->get('Queue.beanstalk');

        $this->connection = Pheanstalk::create($config['host'], $config['port']);
    }

    public function enqueue(object $data, string $queue = 'default')
    {
        $tube = new TubeName($queue);

        $this->connection->useTube($tube);

        $this->calculateDelay($data);

        return $this->connection->put(\json_encode($data), $this->getPriority(), $this->getDelay(), $this->getTtr());
    }

    public function watch(string $queue)
    {
        $tube = new TubeName($queue);
        $this->connection->watch($tube);

        $this->job = $this->connection->reserveWithTimeout(50);

        return $this->job;
    }

    public function removeJob(QueuesJob $job, bool $recreate = false): bool
    {
        $this->connection->delete($this->job);

        if($recreate === true) {
            //$this->connection->release($this->job);
            $job->addAttempt();
            $job->enqueue($job->getQueue());
        }
        $this->job = null;
        return true;
    }

    public function getDataJob()
    {
        $this->connection->touch($this->job);
        return \json_decode($this->job->getData());
    }

    public function setPriority(int $priority)
    {
        $this->priority = $priority;

        return $this;
    }

    public function setTtr(int $ttr)
    {
        $this->ttr = $ttr;

        return $this;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getTtr()
    {
        return $this->ttr;
    }
}
