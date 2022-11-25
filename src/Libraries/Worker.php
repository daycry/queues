<?php

namespace Daycry\Queues\Libraries;

use Pheanstalk\Exception;
use Pheanstalk\Job;
use Daycry\Queues\Config\Queue;
use App\Libraries\Utils;
use CodeIgniter\I18n\Time;
use Queue\Interfaces\TaskInterface;
use CodeIgniter\Debug\Timer;
use Config\Services;

class Worker extends Base
{
    private Timer $benchmark;

    public function __construct(?Queue $config = null)
    {
        $this->benchmark = Services::timer();

        parent::__construct($config);
        $this->_queues();
    }

    public function watch()
    {
        if ($this->pheanstalk) {
            while (true) {
                $job = $this->pheanstalk->reserveWithTimeout(50);
                $result = $this->_getJob($job);
                if (ENVIRONMENT === 'testing') {
                    return $result;
                }
            }
        }
    }

    protected function preActionJob(Job $job = null)
    {
        $this->benchmark->start('job');
    }

    protected function postActionJob(Job $job = null, $result)
    {
        $this->benchmark->stop('job');
    }

    public function listTubes(): array
    {
        return $this->pheanstalk->listTubes();
    }

    private function _getJob(Job $job)
    {
        $data = \json_decode($job->getData());

        $this->preActionJob($job);

        $command = $this->config->jobs[$data->type];
        $type = new $command($data->params);
        $result = $type->execute();

        $this->postActionJob($job, $result);

        $this->pheanstalk->delete($job);

        return $result;
    }

    private function _queues()
    {
        try
        {
            foreach ($this->config->queues as $queue) {
                $this->pheanstalk->watch($queue);
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}