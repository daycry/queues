<?php

namespace Daycry\Queues\Libraries;

use Pheanstalk\Exception;
use Pheanstalk\Job;
use Daycry\Queues\Config\Queue;
use Daycry\Doctrine\Doctrine;
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
                $this->_getJob($job);

                if (ENVIRONMENT === 'testing') {
                    return true;
                }
            }
        }
    }

    public function listTubes(): array
    {
        return $this->pheanstalk->listTubes();
    }

    private function _getJob(Job $job = null)
    {
        if (isset($job)) {
            $this->doctrine = Utils::checkDatabaseConnection($this->doctrine);

            $data = \json_decode($job->getData());

            $class  = (isset($this->config->tasks[$data->action])) ? new $this->config->tasks[$data->action]() : null;

            if($class && $class instanceof TaskInterface)
            {
                $this->benchmark->start('job');

                $task = (isset($data->task) && $data->task !== null) ?
                    $this->doctrine->em->getRepository('\App\Models\Entity\UserSocialNetworkTask')->findOneBy(['id' => $data->task]) : null;

                if ($task) {
                    $task->setInProgress(1);
                    $task->setLastExecution(new Time('now'));
                    $this->doctrine->em->persist($task);
                    $this->doctrine->em->flush();
                }

                // @codeCoverageIgnoreStart
                if (ENVIRONMENT !== 'testing') {
                    $status = $class->initialize($data->params);
                }else{
                    $status = true;
                }
                // @codeCoverageIgnoreEnd

                $this->benchmark->stop('job');

                if ($task) {
                    $task->setInProgress(0);
                    $task->setLastStatus($status);
                    $this->doctrine->em->persist($task);
                    $this->doctrine->em->flush();
                }

                unset($class);
            }

            $this->pheanstalk->delete($job);
        }
    }

    private function _queues()
    {
        $queues = $this->doctrine->em->getRepository('\App\Models\Entity\AutomatonQueue')->findBy(['active' => 1]);

        try {
            if ($queues) {
                foreach ($queues as $queue) {
                    $this->pheanstalk->watch($queue->getName());
                }
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}