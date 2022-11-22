<?php

namespace Daycry\Queues\Libraries;

use Daycry\Queues\Exceptions\DataStructureException;
use Pheanstalk\Exception;
use Pheanstalk\Job;
use Daycry\Queues\Config\Queue;
use Daycry\Queues\Config\QueueValidation;

class Producer extends Base
{
    private string $queue = 'default';
    private string $type;
    private int $delay = 0;
    private int $priority = 10;
    private int $ttr = 3600;
    private ?array $params = []; 

    public function __construct(?Queue $config = null)
    {
        parent::__construct($config);
    }

    public function setQueue(string $queue): self
    {
        $this->queue = $queue;

        return $this;
    }

    public function setDelay(int $delay): self
    {
        $this->delay = $delay;

        return $this;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setTtr(int $ttr): self
    {
        $this->ttr = $ttr;

        return $this;
    }

    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function createJob(): Job
    {
        try {
            $this->_verifyStructure();
            return $this->pheanstalk->useTube($this->queue)->put(\json_encode($this->params), $this->priority, $this->delay, $this->ttr);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    private function _verifyStructure()
    {
        $validator = \Config\Services::validation(config(QueueValidation::class), false);

        $data = array();
        foreach((object)get_class_vars(__CLASS__) as $key => $value) {
            $data[$key] = $this->$key;
        }

        if (!$validator->run($data, 'dataQueue')) {
            throw DataStructureException::validationError($validator->listErrors());
        }
    }
}